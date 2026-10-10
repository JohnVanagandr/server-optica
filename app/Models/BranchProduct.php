<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BranchProduct extends Pivot
{
    // Opcional, pero buena práctica para asegurar que Laravel detecte el nombre correcto
    protected $table = 'branch_product';

    protected $fillable = [
        'branch_id', 
        'product_id', 
        'custom_price', 
        'is_active'
    ];

    // Le decimos a Laravel que maneje los casteos de datos automáticamente
    protected $casts = [
        'is_active' => 'boolean',
        'custom_price' => 'decimal:2',
    ];    
}
