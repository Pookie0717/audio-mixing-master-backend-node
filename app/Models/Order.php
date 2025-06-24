<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount',
        'currency',
        'promocode',
        'payer_name',
        'payer_email',
        'payment_status',
        'Order_status',
        'is_active',
        'payment_method',
        'order_type',
        'order_reference_id',
        'admin_is_read',
        'user_is_read'
    ];

    public function items()
    {
        return $this->hasMany(order_item::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // protected $fillable = [
    //     'user_id',
    //     'gift_order_id',
    //     'user_name',
    //     'user_email',
    //     'user_phone',
    //     'gift_amount',
    //     'services_amount',
    //     'total_amount',
    //     'status',
    //     'payment_status',
    //     'payment_method',
    //     'transaction_id',
    //     'transaction_card_name',
    //     'transaction_card_number',
    //     'transaction_cvc',
    //     'transaction_expiry_year',
    //     'transaction_expiry_month',
    // ];

    // public function services() {
    //     return $this->hasMany(OrderHasService::class, 'order_id', 'id');
    // }

    public function orderItems()
    {
        return $this->hasMany(order_item::class);
    }
    
    public function checkNotification(): int
    {
        $serviceId = order_item::where('order_id', $this->id)->value('service_id');
        
        $revisionExists = Revision::where('order_id', $this->id)
                            ->where('service_id', $serviceId)
                            ->where('user_is_read', 0)
                            ->exists();
                            
        $orderItemExists = order_item::where('order_id', $this->id)
                            ->where('user_is_read', 0)
                            ->exists();
        
        // Return 1 if either condition is true, otherwise 0
        return ($orderItemExists || $revisionExists) ? 1 : 0;
    }
}
