<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gift_id',
        'user_name',
        'user_email',
        'user_phone',
        'balance',
        'promo_code',
        'purchase_price',
        'payment_method',
        'transaction_id',
        'transaction_card_name',
        'transaction_card_number',
        'transaction_cvc',
        'transaction_expiry_year',
        'transaction_expiry_month',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }
}
