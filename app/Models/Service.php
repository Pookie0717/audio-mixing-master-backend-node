<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        "parent_id",
        "category_id",
        "label_id",
        'paypal_plan_id',
        'paypal_product_id',
        'stripe_plan_id',
        'stripe_product_id',
        "name",
        "image",
        "price",
        "discounted_price",
        "service_type",
        "detail",
        "brief_detail",
        "includes",
        "description",
        "requirements",
        "notes",
        "tags",
        "is_active",
        "is_url",
        'is_variation',
        "is_session",
        "detail_data",
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }

    public function childService()
    {
        return $this->hasMany(Service::class, 'parent_id', 'id');
    }
}
