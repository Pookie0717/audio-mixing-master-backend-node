<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Services\StripeService;

class ServiceController extends Controller
{
    
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
            $type = $request->query('type');

            $data = Service::with(['category' => function ($query) {
                $query->where('is_active', 1); // Only fetch category where is_active is 1
            }])
                ->with(['label' => function ($query) {
                    $query->where('is_active', 1); // Only fetch labels where is_active is 1
                }])
                
                ->orderBy('id', 'desc')->where('parent_id', 0);

            if ($type != null && $type == 'one_time') {
                $data = $data->whereNotNull('price');
            }
            if ($type != null && $type == 'monthly') {
                $data = $data->whereNotNull('monthly_price');
            }

            // Check if 'is_active' parameter is present and modify the query accordingly
            if ($isActive === 'active') {
                $data = $data->where('is_active', 1); // Filter for active labels
            } elseif ($isActive === 'inactive') {
                $data = $data->where('is_active', 0); // Filter for inactive labels
            }

            $data = $data->paginate($perPage);

            // if ($data->isEmpty()) throw new Exception('No data found', 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): JsonResponse
    {
        try {
            $data = Service::where('id', $id)
                ->with(['category' => function ($query) {
                    $query->where('is_active', 1); // Only fetch category where is_active is 1
                }])
                ->with(['label' => function ($query) {
                    $query->where('is_active', 1); // Only fetch labels where is_active is 1
                }])
                ->first();
            $sub_data = Service::where('parent_id', $id)->where('is_active',1)
                ->get();
            if (empty($data)) throw new Exception('No data found', 404);
            if(!empty($sub_data)){
                $data->variation = $sub_data;
            }
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        
        
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        try {
            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required|exists:categories,id',
                    'label_id' => 'required|exists:labels,id',
                    'name' => 'required|max:255',
                    'one_time_price' => 'nullable|numeric|required_if:monthly_price,null',
                    'one_time_discounted_price' => 'nullable|numeric',
                    'monthly_price' => 'nullable|numeric|required_if:price,null',
                    'monthly_discounted_price' => 'nullable|numeric',
                    'detail' => 'required',
                    'brief_detail' => 'nullable',
                    'includes' => 'nullable',
                    'description' => 'nullable',
                    'requirements' => 'nullable',
                    'notes' => 'nullable',
                    'tags' => 'nullable',
                    'service_option'=>'required',
                    'is_variation' => 'required|boolean',
                    'product_variation' => 'nullable|required_if:is_variation,1'
                ],
                [
                    'category_id.required' => 'Category required.',
                    'category_id.exists' => 'Invalid category.',

                    'label_id.required' => 'Label required.',
                    'label_id.exists' => 'Invalid label.',

                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    

                    'service_type.required' => 'Service type required.',
                    'service_type.in' => 'Service type only one_time or monthly.',

                    'one_time_price.required_if' => 'Price required.',
                    'one_time_price.numeric' => 'Invalid price.',

                    'one_time_discounted_price.required' => 'Discounted price required.',
                    'one_time_discounted_price.numeric' => 'Invalid discounted price.',

                    'monthly_price.required_if' => 'Monthly price required.',
                    'monthly_price.numeric' => 'Invalid monthly price.',

                    'detail.required' => 'Detail required.',
                    
                    'product_variation.required_if' => 'Variations of product required',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
            $image_name='';
            if($request->is_url == 0){
                $image = $request->file('image');
                $image_name = 'service-images/' . 'service_image_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('service-images'), $image_name);
            }
            else if($request->is_url == 1){
                $image_name = $request->image;
            }

            $parent_id = 0;
            
            
            if ($request->service_option == 'oneTime') {
            
                $data = new Service();
                $data->category_id = $request->category_id;
                $data->label_id = $request->label_id;
                $data->name = $request->name;
                $data->image = $image_name;
                $data->service_type = 'one_time';
                $data->price = $request->one_time_price;
                $data->discounted_price = $request->one_time_discounted_price ?? 0;
                $data->detail = $request->detail;
                $data->brief_detail = $request->brief_detail;
                $data->includes = $request->includes;
                $data->description = $request->description;
                $data->requirements = $request->requirements;
                $data->notes = $request->notes;
                $data->tags = $request->tags;
                $data->is_url = $request->is_url;
                $data->is_active = $request->is_active;
                $data->is_variation = $request->is_variation;
                $data->save();
                $parent_id = $data->id;
                
                if($request->is_variation==1){
                    $productVariations = json_decode($request->product_variation, true);
                    
                    if (is_array($productVariations) && !empty($productVariations)) {
                        foreach($productVariations as $variation){
                            
                            $sub_data = new Service();
                            $sub_data->category_id = $request->category_id;
                            $sub_data->parent_id = $parent_id;
                            $sub_data->label_id = $request->label_id;
                            $sub_data->name = $variation['name'];
                            $sub_data->image = $image_name;
                            $sub_data->service_type = 'one_time';
                            $sub_data->price = $variation['price'];
                            $sub_data->discounted_price = $variation['discounted_price'];
                            $sub_data->detail = $request->detail;
                            $sub_data->brief_detail = $request->brief_detail;
                            $sub_data->includes = $request->includes;
                            $sub_data->description = $request->description;
                            $sub_data->requirements = $request->requirements;
                            $sub_data->notes = $request->notes;
                            $sub_data->tags = $request->tags;
                            $sub_data->is_url = $request->is_url;
                            $sub_data->is_active = $request->is_active;
                            $sub_data->is_variation = 0;
                            $sub_data->save();
                            
                        }
                    }
                }
                
            }

            else if ($request->service_option == 'monthly') {

                    

                $anotherData = new Service();
                $anotherData->parent_id = $parent_id;
                $anotherData->category_id = $request->category_id;
                $anotherData->label_id = $request->label_id;
                $anotherData->name = $request->name;
                $anotherData->image = $image_name;
                $anotherData->service_type = 'subscription';
                $anotherData->price = $request->monthly_price;
                $anotherData->discounted_price = $request->monthly_discounted_price ?? 0;
                $anotherData->detail = $request->detail;
                $anotherData->brief_detail = $request->brief_detail;
                $anotherData->includes = $request->includes;
                $anotherData->description = $request->description;
                $anotherData->requirements = $request->requirements;
                $anotherData->notes = $request->notes;
                $anotherData->tags = $request->tags;
                $anotherData->is_url = $request->is_url;
                $anotherData->is_active = $request->is_active;
                $anotherData->save();
                
                
                /**
                 * Paypal Creation here.
                 */
                $pp_image = '';
                if($request->is_url == 0){
                    $pp_image = 'https://music.zetdigi.com/backend/public/service-images/' . $image_name; 
                }else{
                    $pp_image = $image_name; 
                }
                $cat = Category::find($request->category_id);
                $productResponse = $provider->createProduct([
                    "name" => $request->name,
                    "description" => $request->detail,
                    "type" => "SERVICE",
                    "category" => 'DIGITAL_MEDIA_BOOKS_MOVIES_MUSIC',
                    "image_url" => $pp_image,
                    "home_url" => 'https://check.zetdigi.com/'
                ]);
                    
                if (!isset($productResponse['id'])) {
                    Log::error('PayPal create product failed', $productResponse);
                    return response()->json(['error' => 'PayPal create product failed'], 500);
                }
        
                $productId = $productResponse['id'];
                $anotherData->update(['paypal_product_id' => $productId]);
                
                
                $actuall_price=0;
                if($request->monthly_discounted_price <= 0){
                    $actuall_price=$request->monthly_price;
                }else{
                    $actuall_price=$request->monthly_discounted_price;
                }
                
                
                
                // Create a plan on PayPal
                $planResponse = $provider->createPlan([
                    "product_id" => $productId,
                    "name" =>  $anotherData->name,
                    "description" => 'best plan for you.',
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
                                    "value" => $actuall_price,
                                    "currency_code" => "USD"
                                ]
                            ]
                        ]
                    ],
                    "payment_preferences" => [
                        "auto_bill_outstanding" => true,
                        "setup_fee" => [
                            "value" => 0,
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
                $anotherData->update(['paypal_plan_id' => $planId]);
                
                
                
                
                /**
                 * Stripe Creation here.
                 */


                $stripeProduct = $this->stripeService->createProduct(
                    $request->name,
                    $request->detail
                );

                if (!isset($stripeProduct->id)) {
                    return response()->json(['error' => 'Stripe create product failed'], 500);
                }
                $anotherData->update(['stripe_product_id' => $stripeProduct->id]);
                // Create Plan
                $plan = $this->stripeService->createPlan(
                    $stripeProduct->id,
                    $actuall_price * 100, // Amount in cents
                    'month'
                );
                
                if (!isset($plan->id)) {
                    return response()->json(['error' => 'Stripe create plan failed'], 500);
                }
                $anotherData->update(['stripe_plan_id' => $plan->id]);
            }
            
            else if ($request->service_option == 'both'){
                
                // one time 
                $data = new Service();
                $data->category_id = $request->category_id;
                $data->label_id = $request->label_id;
                $data->name = $request->name;
                $data->image = 'service-images/' . $image_name;
                $data->service_type = 'one_time';
                $data->price = $request->one_time_price;
                $data->discounted_price = $request->one_time_discounted_price ?? null;
                $data->detail = $request->detail;
                $data->brief_detail = $request->brief_detail;
                $data->includes = $request->includes;
                $data->description = $request->description;
                $data->requirements = $request->requirements;
                $data->notes = $request->notes;
                $data->tags = $request->tags;
                $data->save();
                
                // subscription data
                
                $anotherData = new Service();
                $anotherData->parent_id = $parent_id;
                $anotherData->category_id = $request->category_id;
                $anotherData->label_id = $request->label_id;
                $anotherData->name = $request->name;
                $anotherData->image = 'service-images/' . $image_name;
                $anotherData->service_type = 'subscription';
                $anotherData->price = $request->monthly_price;
                $anotherData->discounted_price = $request->monthly_discounted_price ?? null;
                $anotherData->detail = $request->detail;
                $anotherData->brief_detail = $request->brief_detail;
                $anotherData->includes = $request->includes;
                $anotherData->description = $request->description;
                $anotherData->requirements = $request->requirements;
                $anotherData->notes = $request->notes;
                $anotherData->tags = $request->tags;
                $anotherData->save();
                
                
                
                
                // create plan on paypal
                
                $cat = Category::find($request->category_id);
                $productResponse = $provider->createProduct([
                    "name" => $request->name,
                    "description" => $request->description,
                    "type" => "SERVICE",
                    "category" => 'DIGITAL_MEDIA_BOOKS_MOVIES_MUSIC',
                    "image_url" => 'https://music.zetdigi.com/backend/public/service-images/' . $image_name,
                    "home_url" => 'https://check.zetdigi.com/'
                ]);
                    
                if (!isset($productResponse['id'])) {
                    Log::error('PayPal create product failed', $productResponse);
                    return response()->json(['error' => 'PayPal create product failed'], 500);
                }
        
                $productId = $productResponse['id'];
                $anotherData->update(['paypal_product_id' => $productId]);
                
                
                $actuall_price=0;
                if($request->monthly_discounted_price <= 0){
                    $actuall_price=$request->monthly_price;
                }else{
                    $actuall_price=$request->monthly_discounted_price;
                }
                
                
                // Create a plan on PayPal
                $planResponse = $provider->createPlan([
                    "product_id" => $productId,
                    "name" =>  $anotherData->name,
                    "description" => $anotherData->description,
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
                                    "value" => $actuall_price,
                                    "currency_code" => "USD"
                                ]
                            ]
                        ]
                    ],
                    "payment_preferences" => [
                        "auto_bill_outstanding" => true,
                        "setup_fee" => [
                            "value" => 0,
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
                $anotherData->update(['paypal_plan_id' => $planId]);
                
                
                
                $stripeProduct = $this->stripeService->createProduct(
                    $request->name,
                    $request->description
                );

                if (!isset($stripeProduct->id)) {
                    return response()->json(['error' => 'Stripe create product failed'], 500);
                }
                $anotherData->update(['stripe_product_id' => $stripeProduct->id]);
                // Create Plan
                $plan = $this->stripeService->createPlan(
                    $stripeProduct->id,
                    $request->input('amount') * 100, // Amount in cents
                    'month'
                );
                
                if (!isset($plan->id)) {
                    return response()->json(['error' => 'Stripe create plan failed'], 500);
                }
                $anotherData->update(['stripe_plan_id' => $plan->id]);
                
            }
            
            DB::commit();

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id): JsonResponse
    {
        $provider = new PayPalClient();
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
    
        try {
            // Validation
            $validator = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required',
                    'label_id' => 'required',
                    'name' => 'required|max:255',
                    'one_time_price' => 'nullable|numeric|required_if:monthly_price,null',
                    'one_time_discounted_price' => 'nullable|numeric',
                    'monthly_price' => 'nullable|numeric|required_if:one_time_price,null',
                    'monthly_discounted_price' => 'nullable|numeric',
                    'detail' => 'required',
                    'brief_detail' => 'nullable',
                    'includes' => 'nullable',
                    'description' => 'nullable',
                    'requirements' => 'nullable',
                    'notes' => 'nullable',
                    'tags' => 'nullable',
                    'service_option' => 'required|in:oneTime,monthly',
                    'image' => 'nullable|image',
                    'is_url' => 'required|boolean',
                    'is_active' => 'required|boolean',
                    'is_variation' => 'required|boolean',
                    'product_variation' => 'nullable|required_if:is_variation,1',
                    
                ],
                [
                    'category_id.required' => 'Category required.',
                    'category_id.exists' => 'Invalid category.',
                    'label_id.required' => 'Label required.',
                    'label_id.exists' => 'Invalid label.',
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',
                    'service_option.required' => 'Service option required.',
                    'service_option.in' => 'Service option must be oneTime or monthly.',
                    'one_time_price.required_if' => 'One-time price required if monthly price is not provided.',
                    'one_time_price.numeric' => 'Invalid one-time price.',
                    'monthly_price.required_if' => 'Monthly price required if one-time price is not provided.',
                    'monthly_price.numeric' => 'Invalid monthly price.',
                    'detail.required' => 'Detail required.',
                    'is_variation.required' => 'Is variation is required',
                    'product_variation.required_if' => 'Variations of product required',
                ]
            );
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            
            $data = Service::find($id);
            if (!$data) {
                throw new Exception('No data found', 404);
            }
            
            // Handle image upload or URL
            if ($request->is_url == 0 && $request->hasFile('image')) {
                $image = $request->file('image');
                
                $image_name = 'service_image_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('service-images'), $image_name);
                $data->image = 'service-images/' . $image_name;
            } elseif ($request->is_url == 1 && $request->image != '' || $request->image != null) {
                $data->image = $request->image;
            }
    
            // Update data based on service option
            $parent_id = 0;
            $parent_id = $data->id;
            if ($request->service_option == 'oneTime') {
                $data->service_type = 'one_time';
                $data->price = $request->one_time_price;
                $data->discounted_price = $request->one_time_discounted_price ?? null;
            } elseif ($request->service_option == 'monthly') {
                $data->service_type = 'subscription';
                $data->price = $request->monthly_price;
                $data->discounted_price = $request->monthly_discounted_price ?? null;
    
                // PayPal product creation
                
                $pp_image = '';
                if($request->is_url == 0 && $request->image != '' || $request->image != null){
                    $pp_image = 'https://music.zetdigi.com/backend/public/service-images/' . $image_name; 
                }else if ($request->is_url == 1 && $request->image != '' || $request->image != null){
                    $pp_image = $image_name; 
                }
                else{
                    if($data->is_url == 0){
                        $pp_image = 'https://music.zetdigi.com/backend/public/service-images/'. $data->image;
                    }else{
                        $pp_image = $data->image;
                    } 
                }
                $productResponse = $provider->createProduct([
                    "name" => $request->name,
                    "description" => $request->detail,
                    "type" => "SERVICE",
                    "category" => 'DIGITAL_MEDIA_BOOKS_MOVIES_MUSIC',
                    "image_url" => $pp_image,
                    "home_url" => url('/'),
                ]);
    
                if (!isset($productResponse['id'])) {
                    Log::error('PayPal create product failed', $productResponse);
                    return response()->json(['error' => 'PayPal create product failed'], 500);
                }
    
                $productId = $productResponse['id'];
                $data->paypal_product_id = $productId;
    
                $actual_price = $request->monthly_discounted_price > 0 ? $request->monthly_discounted_price : $request->monthly_price;
                // return response()->json($productId);
                $detail = $request->detail;

                // Get the first 6 words
                $shortDetail = implode(' ', array_slice(explode(' ', $detail), 0, 6));
                // Create a PayPal plan
                $planResponse = $provider->createPlan([
                    "product_id" => $productId,
                    "name" => $request->name,
                    "description" => $shortDetail,
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
                                    "value" => $actual_price,
                                    "currency_code" => "USD"
                                ]
                            ]
                        ]
                    ],
                    "payment_preferences" => [
                        "auto_bill_outstanding" => true,
                        "setup_fee" => [
                            "value" => 0,
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
                    return response()->json(['error' => $planResponse, 'message'=> 'PayPal Plan Create Failed'], 500);
                }
    
                $data->paypal_plan_id = $planResponse['id'];
    
                // Stripe product and plan creation
                $stripeProduct = $this->stripeService->createProduct($request->name, $request->detail);
    
                if (!isset($stripeProduct->id)) {
                    return response()->json(['error' => 'Stripe create product failed'], 500);
                }
                $data->stripe_product_id = $stripeProduct->id;
    
                $plan = $this->stripeService->createPlan(
                    $stripeProduct->id,
                    $actual_price * 100, // Amount in cents
                    'month'
                );
    
                if (!isset($plan->id)) {
                    return response()->json(['error' => 'Stripe create plan failed'], 500);
                }
    
                $data->stripe_plan_id = $plan->id;
            }
    
            // General updates applicable for both types
            $data->category_id = $request->category_id;
            $data->label_id = $request->label_id;
            $data->name = $request->name;
            $data->detail = $request->detail;
            $data->brief_detail = $request->brief_detail;
            
            $data->includes = $request->includes;
            $data->description = $request->description;
            $data->requirements = $request->requirements;
            $data->notes = $request->notes;
            $data->tags = $request->tags;
            $data->is_variation = $request->is_variation;
            $data->is_url = $request->is_url;
            $data->is_active = $request->is_active;
            
            // return response()->json($data);
            $data->save();
            
            if($request->is_variation==1){
                $productVariations = json_decode($request->product_variation, true);
                
                if (is_array($productVariations) && !empty($productVariations)) {
                    
                    $variationIdsFromRequest = array_column($productVariations, 'id');
                    $variationIdsFromRequest = array_filter($variationIdsFromRequest); // Remove null values
    
                    // Get existing variations from the database
                    $existingVariations = Service::where('parent_id', $parent_id)->get();
    
                    // Deactivate variations not included in the request
                    foreach ($existingVariations as $existingVariation) {
                        if (!in_array($existingVariation->id, $variationIdsFromRequest)) {
                            $existingVariation->is_active = 0;
                            $existingVariation->save();
                        }
                    }
                    
                    
                    foreach($productVariations as $variation){
                        $sub_data = null;
                        if ($variation['id'] != null) {
                            $sub_data = Service::find($variation['id']);
                        } else {
                            // Handle case when 'id' is null, if needed
                            $sub_data = new Service();
                        }
                        
                        $sub_data->category_id = $request->category_id;
                        $sub_data->parent_id = $parent_id;
                        $sub_data->label_id = $request->label_id;
                        $sub_data->name = $variation['name'];
                        $sub_data->image = $data->image;
                        $sub_data->service_type = 'one_time';
                        $sub_data->price = $variation['price'];
                        $sub_data->discounted_price = $variation['discounted_price'];
                        $sub_data->detail = $request->detail;
                        $sub_data->brief_detail = $request->brief_detail;
                        $sub_data->includes = $request->includes;
                        $sub_data->description = $request->description;
                        $sub_data->requirements = $request->requirements;
                        $sub_data->notes = $request->notes;
                        $sub_data->tags = $request->tags;
                        $sub_data->is_url = $request->is_url;
                        $sub_data->is_active = 1;
                        $sub_data->is_variation = 0;
                        $sub_data->save();
                        
                    }
                }
                else {
                    // Variations array is empty
                    $data->is_variation = 0;
    
                    // Deactivate all existing variations
                    Service::where('parent_id', $parent_id)->update(['is_active' => 0]);
    
                    // Save the parent service again to update is_variation
                    $data->save();
                }
            }
            else {
            // If is_variation is not 1, ensure is_variation is set to 0
                $data->is_variation = 0;
    
                // Deactivate all existing variations
                Service::where('parent_id', $parent_id)->update(['is_active' => 0]);
    
                // Save the parent service again to update is_variation
                $data->save();
            }
    
            return response()->json(['status' => 'success'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request, String $id): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'status' => 'required|boolean',
                ],
                [
                    'status.required' => 'Status required.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = Service::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->is_active = $request->status;
            $data->save();

            // if ($data->parent_id != null) {
            //     $childData = Service::where('id', $data->parent_id)->first();
            //     if (!empty($childData)) {
            //         $childData->is_active = $data->is_active;
            //         $childData->save();
            //     }
            // }

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id): JsonResponse
    {
        try {
            $data = Service::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            if ($data->parent_id != null) {
                $childData = Service::where('id', $data->parent_id)->first();
                if (!empty($childData)) $childData->delete();
            }

            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    
    // list
    
    public function serviceList(): JsonResponse
    {
        try {
            $data = Service::get();

            if ($data->isEmpty()) throw new Exception('No data found', 200);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
}
