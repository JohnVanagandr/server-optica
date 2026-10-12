<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductInteraction extends Model
{
    use HasFactory;

    protected $table = 'product_interactions';

    // 🚀 DESACTIVAMOS LOS TIMESTAMPS SI LA TABLA NO TIENE LAS COLUMNAS
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'product_id',
        'action',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}