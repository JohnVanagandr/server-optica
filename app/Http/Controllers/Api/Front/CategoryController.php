<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Lista las categorías públicas (idealmente solo las activas).
     */
    public function index(): JsonResponse
    {
        try {
            // Traemos las categorías que tengan productos activos
            $categories = Category::where('is_active', true)->get();

            return $this->success(
                $categories, 
                'Categories retrieved successfully', 
                200
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error fetching public categories');
        }
    }
}