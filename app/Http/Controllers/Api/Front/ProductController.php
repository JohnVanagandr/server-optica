<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Models\Advertisement;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse; // Aseguramos usar el trait si usamos $this->success

class ProductController extends Controller
{
    use ApiResponse; // Si $this->success viene de este Trait

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Lista el catálogo público de productos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Filtramos parámetros relevantes para evitar basura en el arreglo de filtros
            $filters = $request->only(['is_featured']);
            
            $products = $this->productService->getCatalogForUser($filters);

            return $this->success(
                ProductResource::collection($products), 
                'Catálogo recuperado exitosamente', 
                200
            );
            
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al consultar el catálogo');
        }
    }
/**
     * Muestra el detalle de un producto público y su respectiva publicidad en cascada.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $productData = $this->productService->getPublicProductById($product);

            // 🔥 NUEVA LÓGICA DE CASCADA ESTRICTA
            $advertisements = collect(); // Colección vacía por defecto

            // Nivel 1: ¿Hay banners específicos para ESTE producto?
            $productAds = \App\Models\Advertisement::where('is_active', true)
                ->where('product_id', $productData->id)
                ->get();

            if ($productAds->isNotEmpty()) {
                $advertisements = $productAds;
            } else {
                // Nivel 2: Si no hay de producto, ¿hay banners para su CATEGORÍA?
                $categoryAds = \App\Models\Advertisement::where('is_active', true)
                    ->where('category_id', $productData->category_id)
                    ->get();

                if ($categoryAds->isNotEmpty()) {
                    $advertisements = $categoryAds;
                } else {
                    // Nivel 3: Si no hay ni de producto ni de categoría, trae los GLOBALES
                    $advertisements = \App\Models\Advertisement::where('is_active', true)
                        ->whereNull('product_id')
                        ->whereNull('category_id')
                        ->get();
                }
            }

            // Adjuntar la URL pública absoluta
            $advertisements->transform(function ($ad) {
                if ($ad->image_path) {
                    $ad->image_url = asset('storage/' . $ad->image_path);
                }
                return $ad;
            });

            // Inyectamos el arreglo resuelto
            $responseData = (new ProductResource($productData))->resolve();
            $responseData['advertisements'] = $advertisements;

            return $this->success(
                $responseData, 
                'Product retrieved successfully', 
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error fetching public product details');
        }
    }   
}