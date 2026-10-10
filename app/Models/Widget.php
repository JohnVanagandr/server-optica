<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'widget_code',
        'title',
        'welcome_message',
        'primary_color',
        'background_color',
        'whatsapp_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}