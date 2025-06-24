<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHasService extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'service_type',
        'image',
        'name',
        'qty',
        'price',
        'total_price',
    ];
}
