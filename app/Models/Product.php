<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

    protected $fillable = ['name', 'slug', 'description', 'price', 'stock', 'sizes', 'active', 'category_id'];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'sizes' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
