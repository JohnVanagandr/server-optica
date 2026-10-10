<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'type',
        'name',
        'price',
        'max_products',
        'max_widgets',
        'description',
        'whatsapp',
    ];

    /**
     * Un plan puede pertenecer a múltiples usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}