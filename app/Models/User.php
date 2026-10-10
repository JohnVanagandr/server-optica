<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\Auth\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'is_active',
        'plan_id',              
        'subscription_active',  
        'subscription_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'subscription_active'  => 'boolean',  // <-- Añadido
            'subscription_ends_at' => 'datetime', // <-- Añadido
        ];
    }

    /**
     * Interceptar y enviar la notificación personalizada de recuperación
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }    
    
    // Un usuario pertenece a UNA sola sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación: Un usuario pertenece a un Plan.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Los likes que este usuario ha dado.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Los productos a los que este cliente le ha dado Like.
     */
    public function likedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_user_like')->withTimestamps();
    }

    // Relación con el Widget (Suponiendo 1 widget por usuario por ahora)
    public function widget()
    {
        return $this->hasOne(Widget::class);
    }

    // Los productos que conforman el catálogo publicado del cliente
    public function catalogProducts()
    {
        return $this->belongsToMany(Product::class, 'product_user')->withTimestamps();
    }
}