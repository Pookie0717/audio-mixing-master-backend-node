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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // $table->foreignId('category_id')->constrained();
            // $table->foreignId('label_id')->constrained();
            // $table->unsignedBigInteger('parent_id')->default(0);
            $table->string('paypal_product_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->string('name');
            $table->string('price')->nullable();
            $table->string('Qty')->nullable();
            $table->string('total_price')->nullable();
            $table->string('service_type');
            
            // $table->string('product_name');
            // $table->decimal('product_price', 10, 2);
            // $table->string('product_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
