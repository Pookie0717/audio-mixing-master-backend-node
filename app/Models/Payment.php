<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "payments";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'payment_id',
        'product_name',
        'quantity',
        'amount',
        'currency',
        'payer_name',
        'payer_email',
        'payment_status',
        'payment_method'
        
    ];
}
