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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // $table->unsignedBigInteger('gift_order_id')->nullable();
            // $table->string('gift_amount')->default(0);
            
            // $table->string('user_name');
            // $table->string('user_email');
            // $table->string('user_phone');
            // $table->string('services_amount');
            // $table->string('total_amount');

            // $table->string('status')->default('received');
            // $table->string('payment_status')->default('unpaid');

            // $table->string('payment_method')->nullable();
            // $table->string('transaction_id')->nullable();
            // $table->string('transaction_card_name')->nullable();
            // $table->string('transaction_card_number')->nullable();
            // $table->string('transaction_cvc')->nullable();
            // $table->string('transaction_expiry_year')->nullable();
            // $table->string('transaction_expiry_month')->nullable();

            $table->string('transaction_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->string('promocode')->nullable();
            // $table->string('payment_id')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payment_status')->nullable();
             $table->boolean('Order_status')->default('Padding');
            $table->boolean('is_active')->default(0);
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
