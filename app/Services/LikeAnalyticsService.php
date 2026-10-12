<?php

namespace App\Services;

use App\Models\Product;

class LikeAnalyticsService
{
    public function getPaginatedStats(int $perPage = 15)
    {
        return Product::query()
            ->select('products.*') // 🚀 Obligatorio para que traiga la columna image_path original
            ->withCount(['favoritedByUsers as active_likes'])
            ->withCount(['cartDraftByUsers as in_cart_count'])
            ->withCount(['publishedWidgetByUsers as in_widget_count'])
            ->withCount(['interactions as total_likes' => function ($query) {
                $query->where('action', 'liked');
            }])
            ->withCount(['interactions as cart_moves_count' => function ($query) {
                $query->where('action', 'moved_to_cart');
            }])
            ->withCount(['interactions as conversions' => function ($query) {
                $query->where('action', 'added_to_widget');
            }])
            ->where(function ($query) {
                $query->has('favoritedByUsers')
                      ->orHas('cartDraftByUsers')
                      ->orHas('publishedWidgetByUsers')
                      ->orHas('interactions');
            })
            ->orderByDesc('total_likes')
            ->orderByDesc('conversions')
            ->paginate($perPage);
    }
}