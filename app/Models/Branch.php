<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches';
    
    protected $fillable = [
        'account_id', 
        'name', 
        'location'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Una sucursal tiene MUCHOS usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }  
    
    // La relación que une los productos con la sucursal (Widget)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'branch_product')
                    ->using(BranchProduct::class) // Le decimos que use nuestro modelo pivote
                    ->withPivot('id', 'custom_price', 'is_active')
                    ->withTimestamps();
    }  
    
    public function activeWidget()
    {
        return $this->hasOne(Widget::class)->where('is_active', true);
    }   
}