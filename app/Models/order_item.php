<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_item extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'order_id',               
        'user_id',               
        'paypal_product_id', 
        'paypal_plan_id',    
        'name',              
        'total_price',
        'quantity',
        'price',             
        'discounted_price', 
        'service_type',      
        'service_id',
        'max_revision',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}