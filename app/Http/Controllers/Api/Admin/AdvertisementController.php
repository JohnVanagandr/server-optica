<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    /**
     * Lista las vallas publicitarias (Paginadas)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Advertisement::with(['category:id,name', 'product:id,name']);

        // Búsqueda por título interno
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $advertisements = $query->latest()->paginate(15);

        // Agregamos la URL pública de la imagen a cada registro
        $advertisements->getCollection()->transform(function ($ad) {
            $ad->image_url = $ad->image_path ? asset('storage/' . $ad->image_path) : null;
            return $ad;
        });

        return $this->success($advertisements, 'Vallas recuperadas correctamente.');
    }

    /**
     * Crea una nueva valla publicitaria
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'image'        => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Máx 2MB
            'redirect_url' => 'nullable|url|max:255',
            'category_id'  => 'nullable|exists:categories,id',
            'product_id'   => 'nullable|exists:products,id',
            'is_active'    => 'boolean',
        ]);

        // Guardamos la imagen en la carpeta storage/app/public/advertisements
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('advertisements', 'public');
        }

        // Si no mandan el estado, lo forzamos a true por defecto
        $validated['is_active'] = $request->boolean('is_active', true);

        $advertisement = Advertisement::create($validated);

        return $this->success($advertisement, 'Valla publicitaria creada exitosamente.', 201);
    }

    /**
     * Actualiza una valla existente
     */
    public function update(Request $request, Advertisement $advertisement): JsonResponse
    {
        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'redirect_url' => 'nullable|url|max:255',
            'category_id'  => 'nullable|exists:categories,id',
            'product_id'   => 'nullable|exists:products,id',
            'is_active'    => 'boolean',
        ]);

        // Si el usuario sube una imagen nueva, borramos la vieja y guardamos la nueva
        if ($request->hasFile('image')) {
            if ($advertisement->image_path) {
                Storage::disk('public')->delete($advertisement->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('advertisements', 'public');
        }

        $advertisement->update($validated);

        return $this->success($advertisement, 'Valla publicitaria actualizada.');
    }

    /**
     * Elimina una valla y su imagen
     */
    public function destroy(Advertisement $advertisement): JsonResponse
    {
        if ($advertisement->image_path) {
            Storage::disk('public')->delete($advertisement->image_path);
        }
        
        $advertisement->delete();

        return $this->success(null, 'Valla eliminada permanentemente.');
    }

    /**
     * Cambia el estado de Activo/Inactivo
     */
    public function toggleStatus(Advertisement $advertisement): JsonResponse
    {
        $advertisement->update(['is_active' => !$advertisement->is_active]);
        
        $status = $advertisement->is_active ? 'activada' : 'desactivada';
        return $this->success($advertisement, "Valla {$status} correctamente.");
    }
}