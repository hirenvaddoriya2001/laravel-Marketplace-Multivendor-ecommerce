<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'product_name',
        'product_sku',
        'product_image',
        'unit_price',
        'quantity',
        'line_total',
        'fulfillment_status',
        'status_updated_at',
        'stock_restored_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'line_total' => 'decimal:2',
        'status_updated_at' => 'datetime',
        'stock_restored_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}