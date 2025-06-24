<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderHasService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::create([
            "user_id" => 1,
            "user_name" => "Ali Jawaid",
            "user_email" => "alijawaidofficial.pk@gmail.com",
            "user_phone" => "03000000000",
            "services_amount" => 150,
            "total_amount" => 150,
            "status" => "Send",
            "payment_status" => "Paid",
            "payment_method" => "stripe",
            "transaction_id" => "transaction-id-195213165485212",
            "transaction_card_name" => "Ali Jawaid",
            "transaction_card_number" => "4242424242424242",
            "transaction_cvc" => "123",
            "transaction_expiry_year" => "25",
            "transaction_expiry_month" => "06",
        ]);

        OrderHasService::create([
            "order_id" => 1,
            "service_id" => 1,
            "service_type" => "one_time",
            "image" => "service-images/1.jpg",
            "name" => "1 Song",
            "qty" => 1, 
            "price" => 150,
            "total_price" => 150, 
        ]);
        
        OrderHasService::create([
            "order_id" => 1,
            "service_id" => 1,
            "service_type" => "monthly",
            "image" => "service-images/1.jpg",
            "name" => "1 Song",
            "qty" => 2, 
            "price" => 150, 
            "total_price" => 300, 
        ]);
    }
}
