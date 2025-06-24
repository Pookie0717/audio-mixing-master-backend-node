<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chat extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "chats";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'senderId',
        'receiverId',
    ];

    /**
     * Indicates whether the model should use timestamp columns.
     *
     * @var bool
     */
    public $timestamps = true; // Add this line to enable timestamps

    // Relationships
    public function sender()
    {
        return $this->belongsTo(User::class, "senderId");
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, "receiverId");
    }
    public function messages()
    {
        return $this->hasmany(message::class, "chatId");
    }
}
