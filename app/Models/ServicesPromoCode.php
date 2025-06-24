<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesPromoCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_id',
        'promo_code',
        'promo_code_balance',
        'expiryDate',
        'time_limit',
        'status'
    ];

    public function ServicesDetails()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
