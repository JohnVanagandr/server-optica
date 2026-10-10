<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name',
        'plan_id',
        'subscription_active',
        'subscription_ends_at',
    ];

    protected $casts = [
        'subscription_active'  => 'boolean',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Relación: Una cuenta pertenece a un Plan.
     * Un plan puede pertenecer a múltiples cuentas/usuarios.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Límite de productos obtenido dinámicamente del plan asignado.
     */
    public function getMaxProductsAttribute(): ?int
    {
        return $this->plan?->max_products;
    }

    /**
     * Límite de widgets obtenido dinámicamente del plan asignado.
     */
    public function getMaxWidgetsAttribute(): ?int
    {
        return $this->plan?->max_widgets;
    }

    // Para contar cuántos productos ha subido esta óptica
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Para obtener el widget publicado
    public function activeWidget()
    {
        return $this->hasOne(Widget::class)->where('status', 'published');
    }

    public function branches() {
        return $this->hasMany(Branch::class);
    }
}