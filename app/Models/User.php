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
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

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
            'subscription_active'  => 'boolean',  
            'subscription_ends_at' => 'datetime', 
        ];
    }

    /**
     * Interceptar y enviar la notificación personalizada de recuperación
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }    
    
    // ==========================================
    // RELACIONES BÁSICAS
    // ==========================================

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function widget()
    {
        return $this->hasOne(Widget::class);
    }

    // ==========================================
    // 1. EL CATÁLOGO PUBLICADO (Tabla: product_user)
    // ==========================================
    
    public function catalogProducts()
    {
        return $this->belongsToMany(Product::class, 'product_user')->withTimestamps();
    }

    // ALIAS: Para que no falle si algún controlador llama a cartProducts()
    public function cartProducts()
    {
        return $this->catalogProducts();
    }

    // ==========================================
    // 2. EL CARRITO BORRADOR (Tabla: cart_user)
    // ==========================================
    
    public function draftCartProducts()
    {
        return $this->belongsToMany(Product::class, 'cart_user')->withTimestamps();
    }

    // ==========================================
    // 3. LOS FAVORITOS / ME GUSTA (Tabla: product_user_like)
    // ==========================================
    
    public function likedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_user_like')->withTimestamps();
    }

    // ALIAS: Para unificar las llamadas de favoriteProducts() y likedProducts()
    public function favoriteProducts()
    {
        return $this->likedProducts();
    }
}