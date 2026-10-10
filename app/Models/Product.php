<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
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
    
    public function likes()
    {
        return $this->belongsToMany(User::class, 'product_user_like')->withTimestamps();
    }  
    
    /**
     * Los widgets en los que este producto está siendo exhibido.
     */
    public function embedWidgets()
    {
        return $this->belongsToMany(EmbedWidget::class, 'embed_widget_product')->withTimestamps();
    }    
}