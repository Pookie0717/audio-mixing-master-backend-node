<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactLeadGeneration extends Model
{
    use HasFactory;
   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "contact_lead_generations";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'name',
        'email',
        'subject',
        'message'
        
    ];
}
