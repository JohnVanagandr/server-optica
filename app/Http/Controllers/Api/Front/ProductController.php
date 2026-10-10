<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
            $products = $this->productService->getCatalogForUser($request->all());

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
     * Muestra el detalle de un producto público.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $product = $this->productService->getPublicProductById($product);

            return $this->success(
                new ProductResource($product), 
                'Product retrieved successfully', 
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error fetching public product details');
        }
    }     
}    