<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Product extends Model
{
    use HasFactory;
    use Sluggable;

            protected $fillable = [
            'user_type',
            'seller_id',
            'name',
            'slug',
            'sku',
            'summary',
            'category',
            'subcategory',
            'price',
            'compare_price',
            'stock_quantity',
            'low_stock_threshold',
            'manage_stock',
            'product_image',
            'visibility',
        ];

        protected $casts = [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'manage_stock' => 'boolean',
            'visibility' => 'boolean',
        ];


    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function images(){
        return $this->hasMany(ProductImage::class,'product_id','id');
    }

    public function getRouteKeyName(): string
        {
            return 'slug';
        }
    public function isInStock(): bool
        {
            return ! $this->manage_stock || $this->stock_quantity > 0;
        }

        public function hasEnoughStock(int $quantity): bool
        {
            if ($quantity < 1) {
                return false;
            }

            return ! $this->manage_stock
                || $this->stock_quantity >= $quantity;
        }

        public function isLowStock(): bool
        {
            return $this->manage_stock
                && $this->stock_quantity > 0
                && $this->stock_quantity <= $this->low_stock_threshold;
        }

        public function getStockStatusAttribute(): string
        {
            if (! $this->manage_stock) {
                return 'available';
            }

            if ($this->stock_quantity < 1) {
                return 'out_of_stock';
            }

            if ($this->isLowStock()) {
                return 'low_stock';
            }

            return 'in_stock';
        }



        public function categoryDetails()
        {
            return $this->belongsTo(
                Category::class,
                'category'
            );
        }

        public function wishlists()
        {
            return $this->hasMany(
                Wishlist::class
            );
        }

        public function reviews()
        {
            return $this->hasMany(
                Review::class
            );
        }
}
