<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'actor_type',
        'actor_id',
        'action',
        'from_value',
        'to_value',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(
            Order::class
        );
    }

    public function item()
    {
        return $this->belongsTo(
            OrderItem::class,
            'order_item_id'
        );
    }
}