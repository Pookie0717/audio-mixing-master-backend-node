<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        // Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setApiKey('sk_test_51MuE4RJIWkcGZUIa0JLTtCVh5g2ZqyqDuXDbxmT4kNqsR1oI2VEOcQXcA6Iojo1yqV7mo2GKMjkTlW76Sk3gVZW400nUHWXlJH');
    }

    public function createProduct($name, $description)
    {
        return Product::create([
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function createPlan($productId, $amount, $interval = 'month')
    {
        return Price::create([
            'unit_amount' => $amount,
            'currency' => 'usd',
            'recurring' => ['interval' => $interval],
            'product' => $productId,
        ]);
    }
    
    public function createPaymentIntent($amount)
    {
        $paymentIntentData = [
            'amount' => $amount,
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];


        $paymentIntent = PaymentIntent::create($paymentIntentData);

        return $paymentIntent->client_secret;
    }
}
