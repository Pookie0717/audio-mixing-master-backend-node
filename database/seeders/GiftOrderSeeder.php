<?php

namespace Database\Seeders;

use App\Models\GiftOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GiftOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GiftOrder::create([
            "user_id" => 1,
            "gift_id" => 1,
            "user_name" => "Ali Jawaid",
            "user_email" => "alijawaidofficial.pk@gmail.com",
            "user_phone" => "03000000000",
            "balance" => 100,
            "promo_code" => "",
            "purchase_price" => 100,
            "payment_method" => "stripe",
            "transaction_id" => "transaction-id-195213165485212",
            "transaction_card_name" => "Ali Jawaid",
            "transaction_card_number" => "4242424242424242",
            "transaction_cvc" => "123",
            "transaction_expiry_year" => "25",
            "transaction_expiry_month" => "06",
        ]);
    }
}
