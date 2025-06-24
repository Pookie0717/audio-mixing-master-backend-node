<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatId');
            $table->foreign('chatId')->references('id')->on('chats');
            $table->unsignedBigInteger('senderId');
            $table->foreign('senderId')->references('id')->on('users')->onDelete('cascade');
            $table->string('message')->nullable();
            $table->string('messageType')->nullable();
             $table->string('image');
             $table->boolean('is_read')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
