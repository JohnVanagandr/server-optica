<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Listar todas las categorías con su respectivo conteo de productos.
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::withCount('products')->get();

            return $this->success(
                CategoryResource::collection($categories),
                'Listado de categorías obtenido con éxito.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al listar las categorías');
        }
    }

    /**
     * Almacenar una nueva categoría con nombre y descripción.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = Category::create($request->validated());

            return $this->success(
                new CategoryResource($category),
                'Categoría creada con éxito.',
                201
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear la categoría');
        }
    }

    /**
     * Mostrar una categoría específica.
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');

        return $this->success(
            new CategoryResource($category),
            'Detalle de la categoría obtenido con éxito.'
        );
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $category->update($request->validated());

            return $this->success(
                new CategoryResource($category),
                'Categoría actualizada con éxito.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar la categoría');
        }
    }

    /**
     * Eliminar una categoría validando que no tenga productos activos.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if ($category->products()->exists()) {
                return $this->error(
                    'No puedes eliminar esta categoría porque contiene productos asociados.',
                    422
                );
            }

            $category->delete();

            return $this->success([], 'Categoría eliminada con éxito.');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar la categoría');
        }
    }
}