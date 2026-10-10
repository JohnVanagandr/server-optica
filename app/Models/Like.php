<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        'product_id', 
        'user_id'
    ];

    // Desactivamos 'updated_at' para respetar el diseño de la base de datos
    public const UPDATED_AT = null;

    // Relación: Un "Like" pertenece a un Producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Los usuarios que le han dado Like a este producto.
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'product_user_like')->withTimestamps();
    }
}