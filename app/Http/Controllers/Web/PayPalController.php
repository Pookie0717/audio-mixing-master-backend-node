<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\order_item;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Coupon;
use Illuminate\Support\Facades\Mail;
// use Auth;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Cart;
use App\Models\Subscription;
use Exception;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\User\OrderSuccessMail;
use App\Mail\User\OrderStatusMail;
use App\Mail\User\GiftCardMail;
use App\Mail\Admin\OrderRevisionMail;
use App\Mail\Admin\OrderMail;
use App\Mail\Admin\EngineerOrderMail;
use App\Mail\User\RevisionSuccessMail;
use App\Models\OrderCoupon;
use App\Models\OrderFile;
use App\Models\UserWallet;
use App\Models\Revision;
use Stripe\StripeClient;
use Stripe\Stripe;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Checkout\Session as CheckoutSession;
use Illuminate\Pagination\LengthAwarePaginator;

class PayPalController extends Controller
{
    protected $user;
    protected $adminurl;
    protected $userurl;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->adminurl = env('REACT_BACKEND_URL', 'https://fallback-url.com');
        $this->userurl = env('REACT_FRONTEND_URL', 'https://fallback-url.com');
    }
    
    
    // Existing method for one-time payment
    public function paypal(Request $request)
    {
        
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('success', [
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'promocode' => $request->promocode,
                    'cartItem' => json_encode($request->cartItem),
                    'user_id' => $request->userId,
                ]),
                "cancel_url" => route('cancel', [
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'promocode' => $request->promocode,
                    'cartItem' => json_encode($request->cartItem)
                ])
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $request->currency,
                        "value" => $request->amount
                    ],
                    "description" => $request->description
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            Log::info('PayPal create order response', $response);

            // Searching for the 'approve' link in the response
            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return response()->json([
                        'approve_link' => $link['href']  // Returning only the 'approve' link
                    ]);
                }
            }
            // If no 'approve' link found, log error and redirect
            Log::error('No approve link found in PayPal response', $response);
            return redirect()->route('cancel');
        } else {
            Log::error('PayPal create order failed', $response);
            return redirect()->route('cancel');
        }
    }
    
    public function Stripe(Request $request)
    {
        return response()->json($request);
        Stripe::setApiKey('sk_test_51MuE4RJIWkcGZUIa0JLTtCVh5g2ZqyqDuXDbxmT4kNqsR1oI2VEOcQXcA6Iojo1yqV7mo2GKMjkTlW76Sk3gVZW400nUHWXlJH');
    
        $cart = $request->cartItem;
        $productItems = [];
    
        // Loop through each item in the cart
        foreach ($cart as $item) {
            $productItems[] = [
                'price_data' => [
                    'product_data' => [
                        'name' => $item['service_name'],
                    ],
                    'currency' => 'USD',
                    'unit_amount' => $item['price'],
                ],
                'quantity' => $item['qty'],
            ];
        }
    
        $session = CheckoutSession::create([
            'line_items' => $productItems,
            'mode' => 'payment',
            'allow_promotion_codes' => false,
            'metadata' => [
                'user_id' => Auth::user()->id,
            ],
            'customer_email' => Auth::user()->email, // Fixed the typo from 'emil' to 'email'
            'success_url' => route('success', [
                'amount' => $request->amount,
                'currency' => $request->currency,
                'promocode' => $request->promocode,
                'cartItem' => json_encode($request->cartItem),
                'user_id' => $request->userId,
                'transaction_id' => $session->id,
                'payer_name' => Auth::user()->name,
                'payer_email' => Auth::user()->email,
            ]),
            'cancel_url' => route('cancel', [
                'amount' => $request->amount,
                'currency' => $request->currency,
                'promocode' => $request->promocode,
                'cartItem' => json_encode($request->cartItem),
                'transaction_id' => $session->id,  // Send transaction ID to cancel route
            ]),
        ]);
    
        return redirect()->away($session->url);
    }



    // Method for handling subscription
    public function createSubscription(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // Request se category_id lena
        $category_id = $request->category_id;

        // Categories table se category ka naam nikalna
        $category = Category::find($category_id);

        if (!$category) {
            // Agar category nahi milti, to error throw karo
            return response()->json(['error' => 'Category not found'], 404);
        }


        // Save subscription details in the database
        $subscription = Service::create([
            'parent_id' => $request->parent_id,
            'category_id' => $request->category_id,
            'label_id' => $request->label_id,
            'name' => $request->name,
            'image' => $request->image,
            'price' => $request->price,
            'discounted_price' => $request->discounted_price,
            'service_type' => $request->service_type,
            // 'detail' => $request->detail,
            'brief_detail' => $request->brief_detail,
            'includes' => $request->includes,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'notes' => $request->notes,
            'tags' => $request->tags,
            'is_active' => 1,
            'paypal_product_id' => '', // to be updated later
            'paypal_plan_id' => '', // to be updated later
        ]);
        // $subscription = Subscription::create([
        //     "name" => $request->name,
        //     "description" => $request->description,
        //     "type" => $request->type,
        //     "category" => $category->name, // Category naam use karna
        //     "price" => $request->price,
        //     "image_url" => $request->image_url,
        //     "home_url" => $request->home_url,
        //     "paypal_product_id" => '', // to be updated later
        // ]);

        // Create a product on PayPal
        $productResponse = $provider->createProduct([
            "name" => $request->name,
            "description" => $request->description,
            "type" => $request->service_type,
            // "category" => $category->name, // Category naam use karna
            "image_url" => $request->image,
            "home_url" => $subscription->home_url
        ]);

        if (!isset($productResponse['id'])) {
            Log::error('PayPal create product failed', $productResponse);
            return response()->json(['error' => 'PayPal create product failed'], 500);
        }

        $productId = $productResponse['id'];
        $subscription->update(['paypal_product_id' => $productId]);

        // Create a plan on PayPal
        $planResponse = $provider->createPlan([
            "product_id" => $productId,
            "name" =>  $subscription->name,
            "description" => $subscription->description,
            "billing_cycles" => [
                [
                    "frequency" => [
                        "interval_unit" => "MONTH",
                        "interval_count" => 1
                    ],
                    "tenure_type" => "REGULAR",
                    "sequence" => 1,
                    "total_cycles" => 0,
                    "pricing_scheme" => [
                        "fixed_price" => [
                            "value" => $request->price,
                            "currency_code" => "USD"
                        ]
                    ]
                ]
            ],
            "payment_preferences" => [
                "auto_bill_outstanding" => true,
                "setup_fee" => [
                    "value" => $request->price,
                    "currency_code" => "USD"
                ],
                "setup_fee_failure_action" => "CONTINUE",
                "payment_failure_threshold" => 3
            ],
            "taxes" => [
                "percentage" => "0",
                "inclusive" => false
            ]
        ]);

        if (!isset($planResponse['id'])) {
            Log::error('PayPal create plan failed', $planResponse);
            return response()->json(['error' => 'PayPal create plan failed'], 500);
        }

        $planId = $planResponse['id'];
        $subscription->update(['paypal_plan_id' => $planId]);

        // Create a subscription on PayPal
        $subscriptionResponse = $provider->createSubscription([
            "plan_id" => $planId,
            "start_time" => now()->addMinutes(5)->toIso8601String(),
            "subscriber" => [
                "name" => [
                    "given_name" => auth()->user('first_name'),
                    "surname" => auth()->user('last_name')
                ],
                "email_address" => auth()->user('email')
            ],
            "application_context" => [
                "brand_name" => "Your Brand",
                "locale" => "en-US",
                "shipping_preference" => "SET_PROVIDED_ADDRESS",
                "user_action" => "SUBSCRIBE_NOW",
                "payment_method" => [
                    "payer_selected" => "PAYPAL",
                    "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
                ]
            ]
        ]);

        if (isset($subscriptionResponse['id']) && $subscriptionResponse['id'] != null) {
            // Log the response for debugging
            Log::info('PayPal create subscription response', $subscriptionResponse);

            foreach ($subscriptionResponse['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return response()->json([
                        'message' => 'Subscription created successfully',
                        'subscription' => $subscription,
                        'approval_link' => $link['href']
                    ]);
                }
            }
        } else {
            Log::error('PayPal create subscription failed', $subscriptionResponse);
            return response()->json(['error' => 'PayPal create subscription failed'], 500);
        }
    }

    // New method for getting subscription details
    public function getOrderDetails(Request $request)
    {
        try {
            
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
            $orderStatus = $request->query('order_status');
            $orderType = $request->query('order_type');
            $searchQuery = $request->query('search');
            
            // Initialize the query using the Order model
            $query = Order::with('orderItems')
                ->orderBy('id', 'desc');

            // Filter by 'is_active' if the parameter is present
            if ($isActive === 'active') {
                $query = $query->where('is_active', 1);
            } elseif ($isActive === 'inactive') {
                $query = $query->where('is_active', 0);
            }

            // Filter by 'Order_status' if the parameter is present
            if ($orderStatus != '') {
                $query = $query->where('Order_status', $orderStatus);
            }
            // dd($orderStatus);
            if (!empty($orderType)) {
                $query = $query->where('order_type', $orderType);
            }
            // Filter by user name or email if the search query is present
            if (!empty($searchQuery)) {
                // Check if the search query is numeric to search by order ID
                if (is_numeric($searchQuery)) {
                    $query = $query->where('id', $searchQuery);
                } else {
                    // Otherwise, search by user name or email
                    $userIds = User::where('first_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%')
                        ->pluck('id')
                        ->toArray();
    
                    // Filter orders by the found user IDs
                    $query = $query->whereIn('user_id', $userIds);
                }
            }
            // Execute the query with pagination
            $data = $query->paginate($perPage);

            // Check if data is empty and return appropriate response
            if ($data->isEmpty()) {
                return response()->json(['message' => 'No orders found'], 200);
            }
            
            $data->getCollection()->transform(function ($order) {
                $serviceIds = order_item::where('order_id', $order->id)->pluck('service_id');
                
                $revisionExists = Revision::where('order_id', $order->id)
                    ->whereIn('service_id', $serviceIds)
                    ->where('admin_is_read',0)
                    ->exists();
                    
                $order_itemExists = order_item::where('order_id', $order->id)->where('admin_is_read',0)->exists();
                    // return response()->json($revisionExists);
                $order->notify = ($order_itemExists || $revisionExists) ? 1 : 0;
                
                $user = User::find($order->user_id); // Find the user by user_id
                if ($user) {
                    $order->username = $user->first_name . ' ' . $user->last_name;
                    $order->useremail = $user->email;
                } else {
                    $order->username = 'Unknown User';
                    $order->useremail = 'Unknown Email';
                }
                return $order;
            });
            
            

            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    //update status

    
    // order by id
    public function orderDetails($id){
        try {
            // Retrieve the order by id
            $order = Order::find($id);
            
            if (!$order) {
                throw new Exception('No order found', 200);
            }
            
            // Retrieve the associated order items
            $orderItems = order_item::where('order_id', $id)->get();
            
            $Coupon=null;
            if($order->promocode != null){
                $Coupon = OrderCoupon::where('code', $order->promocode)->where('order_id',$id)->first();
                
            }
            $user = User::find($order->user_id);
            $revision = Revision::where('order_id',$id)->get();
            $username = $user->first_name.' '.$user->last_name;
            $useremail = $user->email;
            
            $serviceIds = $orderItems->pluck('service_id')->unique();

            // Check if any service has category_id == 15
            $hasGiftcard = Service::whereIn('id', $serviceIds)
                                  ->where('category_id', 15)
                                  ->exists();
    
            // Set is_giftcard
            $is_giftcard = $hasGiftcard ? 1 : 0;
        
            $orderDetails = [
                'order' => $order,
                'order_items' => $orderItems,
                'coupon' => $Coupon,
                'user_name'=> $username,
                'user_email'=>$useremail,
                'revision' => $revision ? $revision : null,
                'is_giftcard' =>$is_giftcard
            ];
    
            return response()->json($orderDetails, 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    // orders by user_id
    public function userOrders($user_id)
    {
    try {
        // Retrieve the orders by user_id
        $orders = Order::where('user_id', $user_id)->get();
        
        if ($orders->isEmpty()) {
            throw new Exception('No order found', 404);
        }
        
        // Prepare the order details with associated order items
        $orderDetails = $orders->map(function ($order) {
            $orderItems = order_item::where('order_id', $order->id)->get();
            return [
                'order' => $order,
                'order_items' => $orderItems
            ];
        });
    
        return response()->json($orderDetails, 200);
    } catch (Exception $e) {
        // Return an error response if an exception occurs
        return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
    }
}

    public function success(Request $request)
    {
        // Validate request data
        $request->validate([
            'user_id' => 'required|integer',
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric',
            'payer_name' => 'required|string',
            'payer_email' => 'required|email',
            'order_type' => 'required',
            'payment_method' => 'required',
            'cartItems' => 'required|array',
            'cartItems.*.service_id' => 'required',
            'cartItems.*.service_name' => 'required|string',
            'cartItems.*.qty' => 'required',
            'cartItems.*.price' => 'required|numeric',
            'cartItems.*.total_price' => 'required|numeric',
            'cartItems.*.service_type' => 'required|string',
        ]);
    
        try {
            // Retrieve and prepare data from the request'
            $user = User::find($request->user_id);
            $amount = $request->amount;
            $promocode = $request->promoCode ?? null;
            $cartItems = $request->cartItems;
            
            // Insert order data into the database
            $order = Order::create([
                'user_id' => $request->user_id,
                'transaction_id' => $request->transaction_id,
                'amount' => $amount,
                'currency' => 'USD',
                'promocode' => $promocode,
                'Order_status' => 0,
                'is_active' => 1,
                'payer_name' => $request->payer_name,
                'payer_email' => $request->payer_email,
                'payment_status' => "PAID",
                'payment_method' => $request->payment_method,
                'order_type' => $request->order_type,
                'order_reference_id' => $request->order_id ?? null
            ]);

            // Process each cart item and insert data into the order_item table
            $t_amount = 0;
            foreach ($cartItems as $item) {
                
                
                
            $service = Service::where('id', $item['service_id'])->first();

                // Check if the service is a gift card (by checking category_id)
                if ($service && $service->category_id == '15') {
                    // Create user_wallet entry for the gift card
                    $g_code = 'gift-' . Str::upper(Str::random(10));
                    UserWallet::create([
                        'user_id' => $request->user_id,
                        'promocode' => $g_code,
                        'amount' => $item['price'],
                    ]);
                    
                    Mail::to($user->email)->send(new GiftCardMail([
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'message' => 'Thank you for your purchase. your gift card amount is:'.$item['price'].'and your code is:',
                        'code'=> $g_code,
                        
                    ]));
                }
            
            
            
                order_item::create([
                    'order_id' => $order->id,
                    'service_id' => $item['service_id'],
                    'name' => $item['service_name'],
                    'price' => $item['price'],
                    'quantity' => $item['qty'],
                    'max_revision' => $item['qty']*3,
                    'total_price' => $item['total_price'],
                    'service_type' => $item['service_type'],
                    'paypal_product_id'=> $item['paypal_product_id'] ?? null,
                    'paypal_plan_id'=> $item['paypal_plan_id'] ?? null,
                ]);
                $t_amount = $t_amount + $item['total_price'];
            }
            
            // Applying coupon
            if($promocode != null){
                if (strpos($promocode, 'gift-') !== false) {
                    $user_wallet = UserWallet::where('promocode', $promocode)->first();

                    // Calculate how much can actually be used from the wallet without going negative
                    $usable_amount = min($user_wallet->amount, $t_amount);
                    
                    // Update the used amount with only the amount that can be applied
                    $user_wallet->use_amount += $usable_amount;
                    
                    // Subtract the used amount from the wallet, ensuring the amount doesn't go below zero
                    $user_wallet->amount = max(0, $user_wallet->amount - $t_amount);
                    
                    // Save the updated wallet
                    $user_wallet->save();
                    
                    
                    
                }else{
                    $user_coupon = new UserCoupon;
                
                    $user_coupon->user_id = $request->user_id;
                    $user_coupon->coupon_code = $promocode;
                    $user_coupon->used_at = Carbon::now()->format('Y-m-d');
                    $user_coupon->save();
                    
                    $coupon = Coupon::where('code', $promocode)->first();
                    $coupon->uses = $coupon->uses+1;
                    $coupon->save();
                    
                    $Order_coupon = new OrderCoupon;
                    $Order_coupon->order_id = $order->id;
                    $Order_coupon->code = $coupon->code;
                    $Order_coupon->discount_type = $coupon->discount_type;
                    $Order_coupon->discount_value = $coupon->discount_value;
                    $Order_coupon->product_ids = $coupon->product_ids ?? null;
                    $Order_coupon->save();
                }
            }
    
            // Removing items from cart if payment type is one_time
            if ($request->payment_type == "one_time") {
                foreach ($cartItems as $item) {
                    $cart_data = Cart::where('service_id', $item['service_id'])
                        ->where('user_id', $request->user_id)
                        ->first();
                    if ($cart_data != null) {
                        $cart_data->delete();
                    }
                }
            }
    
    
    
    
    
            // Send order success mail
            
            $orderStatusMessage = '';
            switch ($request->order_status) {
                    case 0:
                        $orderStatusMessage = 'Pending';
                        break;
                    case 1:
                        $orderStatusMessage = 'In Process';
                        break;
                    case 2:
                        $orderStatusMessage = 'Delivered';
                        break;
                    case 3:
                        $orderStatusMessage = 'Canceled';
                        break;
                    default:
                        $orderStatusMessage = 'Unknown';
                        break;
                }
            $orderItems = order_item::where('order_id',$order->id)->get();
            
            // mail to user
            Mail::to($user->email)->send(new OrderSuccessMail([
                'name' => $user->first_name . ' ' . $user->last_name,
                'order_id' => $order->id,
                'message' => 'Thank you for your purchase. Your order has been processed successfully. Your order details are as follows',
                'items'=> $orderItems,
                'url' => $this->userurl . '/order/' . $order->id,
                
            ]));
            
            // mail to admin
            $admin = User::where('role','admin')->first();
            Mail::to($admin->email)->send(new OrderSuccessMail([
                'name' => $admin->first_name . ' ' . $admin->last_name,
                'order_id' => $order->id,
                'items'=> $orderItems,
                'message' => 'New Order Arrived. All Engineer has been notified',
                'url' => $this->adminurl . '/order-detail/' . $order->id,
            ]));
            
            // mail to engineer
            
            
            $engineers = User::where('role','engineer')->get();
            foreach($engineers as $engineer){
                
                Mail::to($engineer->email)->send(new EngineerOrderMail([
                    'name' => $engineer->first_name . ' ' . $engineer->last_name,
                    'order_id' => $order->id,
                    'items'=> $orderItems,
                    'url' => $this->adminurl . '/order-detail/' . $order->id,
                    'message' => 'New Order Arrived. Click the link blow and go to the dashboard.',
                ]));
            }
            
            
            
            
            
            // Return success response
            return response()->json(['message' => 'success','order_id' => $order->id], 200);
    
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
  
    public function cancel(Request $request)
    {
        Log::info('Payment cancelled', $request->all());
        return "Payment is cancelled.";
    }
    
    
    // succes route for 
    
    public function revisionSuccess(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request
            $validator = Validator::make(
                $request->all(),
                [
                    'order_id' => 'required|numeric',
                    'service_id' => 'required|numeric',
                    'user_id' => 'required|numeric',
                    'amount' => 'required|numeric',
                    'transaction_id' =>'required',
                    'payer_name' =>'required',
                    'payer_email' =>'required'
                ]
            );
    
            // If validation fails, throw an exception with the first error
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
    
            // Retrieve the order item by order_id and service_id
            $orderItem = order_item::where('order_id', $request->order_id)
                ->where('service_id', $request->service_id)
                ->first();
    
            if (!$orderItem) {
                throw new Exception('Order item not found', 404);
            }
    
            // Create a new Revision
            $revision = new Revision();
            $revision->order_id = $request->order_id;
            $revision->service_id = $request->service_id;
            $revision->user_id =$request->user_id;
            $revision->transaction_id = $request->transaction_id;
            $revision->amount = $request->amount;
            $revision->payer_name = $request->payer_name;
            $revision->payer_email = $request->payer_email;
            $revision->status = 'paid';
    
            // Increase the max_revision count
            $orderItem->max_revision = $orderItem->max_revision + 1;
           
    
            // Save the Revision
            $revision->save();
            // Save the updated order item
            $orderItem->save();
            $admin = User::where('role','admin')->first();
            // send mail to admin
            Mail::to($admin->email)->send(new RevisionSuccessMail([
                'name' => $admin->first_name . ' ' . $admin->last_name,
                'order_id' => $request->order_id,
                'service_id' => $request->service_id,
                'amount' => $request->amount,
                'message' => 'User successfully Purchased a Revision from your website.',
                'url'=> $this->adminurl . '/order-detail/' . $request->order_id,
                
            ]));
            // send mail to user
            $user = User::find($request->user_id);
            Mail::to($user->email)->send(new RevisionSuccessMail([
                'name' => $user->first_name . ' ' . $user->last_name,
                'order_id' => $request->order_id,
                'service_id' => $request->service_id,
                'amount' => $request->amount,
                'message' => 'Thanks you for your purchased.',
                'url'=> $this->userurl . '/order/' . $request->order_id,
                
            ]));
            // Return a success response
            return response()->json(['message' => "success",'max_count'=>$orderItem->max_revision,"revision"=>$revision], 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // stripe payment intent function
    
    
    public function createPaymentIntent(Request $request)
    {
        // Initialize Stripe client with your secret key
        $stripe = new StripeClient('sk_test_51MuE4RJIWkcGZUIa0JLTtCVh5g2ZqyqDuXDbxmT4kNqsR1oI2VEOcQXcA6Iojo1yqV7mo2GKMjkTlW76Sk3gVZW400nUHWXlJH');

        // Validate the request data
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);
        
        // Create a payment intent and retrieve the client secret
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $request->input('amount') * 100, // Convert amount to cents
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        // Return the client secret in the response
        return response()->json($paymentIntent->client_secret);
    }

    
    public function createSubscriptionStripe(Request $request)
    {
        $customerEmail = $request->input('user_email');
        $customerName = $request->input('user_name');
        $priceId = $request->input('plan_id');
        $stripe = new StripeClient('sk_test_51MuE4RJIWkcGZUIa0JLTtCVh5g2ZqyqDuXDbxmT4kNqsR1oI2VEOcQXcA6Iojo1yqV7mo2GKMjkTlW76Sk3gVZW400nUHWXlJH');
        try {
            // Create or retrieve the customer
            $customer = $stripe->customers->create([
                'email' => $customerEmail,
                'name' => $customerName
            ]);

            // Create the subscription
            $subscription = $stripe->subscriptions->create([
                'customer' => $customer->id,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            // Send the subscription ID and client secret to the client
            return response()->json(["intent"=>$subscription->latest_invoice->payment_intent->client_secret, "subscription_id"=>$subscription->id]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create subscription',
            ], 500);
        }
    }

}
