<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(): JsonResponse
    {
        try {
            $products = Product::with('category')->latest()->get();

            return $this->success(
                ProductResource::collection($products),
                'Listado de productos obtenido con éxito.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al listar los productos');
        }
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct(
                $request->validated(),
                $request->file('image')
            );
            $product->load('category');

            return $this->success(
                new ProductResource($product),
                'Producto creado exitosamente.',
                201
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear el producto');
        }
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return $this->success(
            new ProductResource($product),
            'Detalle del producto obtenido con éxito.'
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->updateProduct(
                $product,
                $request->validated(),
                $request->file('image')
            );
            $updatedProduct->load('category');

            return $this->success(
                new ProductResource($updatedProduct),
                'Producto actualizado exitosamente.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar el producto');
        }
    }

    public function toggleFeatured(Product $product): JsonResponse
    {
        try {
            $product->is_featured = !$product->is_featured;
            $product->save();

            return $this->success(
                new ProductResource($product),
                $product->is_featured ? 'Producto marcado como destacado.' : 'Producto removido de destacados.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cambiar la visibilidad del producto');
        }
    }

    public function toggleActive(Product $product): JsonResponse
    {
        try {
            // Regla de Negocio: Validar si está en uso por clientes de pago antes de desactivar
            if ($product->is_active) {
                $enUsoPorClientesPagos = $product->widgetUsers()->where('subscription_active', true)->exists();

                if ($enUsoPorClientesPagos) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'No se puede desactivar: El producto está siendo utilizado en el probador de clientes de pago.'
                    ], 422);
                }
            }

            $product->is_active = !$product->is_active;
            $product->save();

            return $this->success(
                new ProductResource($product),
                $product->is_active ? 'Producto activado con éxito.' : 'Producto desactivado con éxito.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cambiar el estado del producto');
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        try {
            // Regla de Negocio: Evitar eliminación si está en el widget de clientes con suscripción
            $enUsoPorClientesPagos = $product->widgetUsers()->where('subscription_active', true)->exists();

            if ($enUsoPorClientesPagos) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No se puede eliminar: El producto está actualmente publicado en el catálogo de uno o más clientes con suscripción activa.'
                ], 422);
            }

            $this->productService->deleteProduct($product);

            return $this->success([], 'Producto y fotografía eliminados correctamente.');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar el producto');
        }
    }
}