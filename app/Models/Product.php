<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasImage;

class Product extends Model
{
    use HasFactory;
    use HasImage; // 🚀 El Trait inteligente que creamos
    
    protected $fillable = [
        'name',
        'category_id',
        'base_price',
        'sku',
        'brand',
        'image_path',
        'ar_model_url',
        'web_tracking_url',
        'scale_x',
        'scale_y',
        'is_active',
        'is_featured',
    ];

    // 🚀 FIX 1 (Prevención de bug en React): 
    // Inyectamos 'image_url' (del Trait) en lugar de 'image_path'. 
    // Así React siempre recibe la URL completa y absoluta.
    protected $appends = ['image_url'];

    // Un producto pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
                    ->using(BranchProduct::class)
                    ->withPivot('id', 'custom_price', 'is_active')
                    ->withTimestamps();
    }  
    
    /**
     * Relación Operativa: Usuarios que ACTUALMENTE tienen este producto en favoritos.
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes')->withPivot('created_at');
    }  

    /**
     * Relación Analítica: Todo el historial de interacciones del producto.
     */
    public function interactions()
    {
        return $this->hasMany(ProductInteraction::class);
    }
    
    /**
     * Relación Operativa: Usuarios que tienen este producto en su widget/catálogo.
     */
    public function widgetUsers()
    {
        return $this->belongsToMany(User::class, 'product_user');
    }

    /**
     * Usuarios que tienen este producto en su carrito borrador temporal
     * Tabla: cart_user
     */
    public function inCartByUsers()
    {
        return $this->belongsToMany(User::class, 'cart_user')->withTimestamps();
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'product_user_like')->withTimestamps();
    }

    public function cartDraftByUsers()
    {
        return $this->belongsToMany(User::class, 'cart_user')->withTimestamps();
    }

    public function publishedWidgetByUsers()
    {
        return $this->belongsToMany(User::class, 'product_user')->withTimestamps();
    }
}