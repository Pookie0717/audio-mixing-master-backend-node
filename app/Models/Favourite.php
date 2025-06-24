<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id'
    ];

    public function ServiceDetails()
    {
        return $this->belongsTo(Service::class,"service_id");
    }
    public function label()
{
    return $this->belongsTo(Label::class, 'label_id');
}
}
