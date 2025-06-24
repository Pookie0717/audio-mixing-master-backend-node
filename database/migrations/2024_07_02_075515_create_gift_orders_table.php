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
        Schema::create('gift_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('gift_id')->constrained();

            $table->string('user_name');
            $table->string('user_email');
            $table->string('user_phone');

            $table->string('balance');
            $table->string('promo_code')->unique();

            $table->string('purchase_price')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_card_name')->nullable();
            $table->string('transaction_card_number')->nullable();
            $table->string('transaction_cvc')->nullable();
            $table->string('transaction_expiry_year')->nullable();
            $table->string('transaction_expiry_month')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_orders');
    }
};
