<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = Cart::select(
                'id',
                'service_id',
                'qty',
                'price',
                'total_price'
            )->with([
                    'service' => function ($query) {
                        $query->select(
                            'id',
                            'category_id',
                            'name',
                            'image',
                            'service_type',
                            'price',
                            'discounted_price',
                            'paypal_product_id',
                            'paypal_plan_id'
                        );
                    },
                    'service.category' => function ($query) {
                        $query->select(
                            'id',
                            'name'
                        );
                    }
                ])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

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
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'services' => 'required|array',
                    'services.*.id' => 'required|exists:services,id',
                    'services.*.qty' => 'required|numeric',
                    'services.*.price' => 'required|numeric',
                    'services.*.total_price' => 'required|numeric',
                ],
                [
                    'services.required' => 'Services required.',
        
                    'services.id.required' => 'Service id required.',
                    'services.id.exists' => 'Service id invalid.',
        
                    'services.qty.required' => 'Quantity required.',
                    'services.qty.numeric' => 'Quantity invalid.',
        
                    'services.price.required' => 'Price required.',
                    'services.price.numeric' => 'Price invalid.',
        
                    'services.total_price.required' => 'Total price required.',
                    'services.total_price.numeric' => 'Total price invalid.',
                ]
            );
        
            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
        
            $user = Auth::user();
        
            foreach ($request->services as $service) {
                Cart::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'service_id' => $service['id'],
                    ],
                    [
                        'qty' => $service['qty'],
                        'price' => $service['price'],
                        'total_price' => $service['total_price'],
                    ]
                );
            }
        
            $data = Cart::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->join('services', 'services.id', '=', 'carts.service_id')
            ->select('carts.*', 'services.name', 'services.service_type', 'services.paypal_plan_id' , 'services.paypal_product_id')
            ->get();
        
            return response()->json($data, 200);
        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(String $service_id, Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'qty' => 'required|numeric',
                    'price' => 'required|numeric',
                    'total_price' => 'required|numeric',
                ],
                [
                    'qty.required' => 'Quantity required.',
                    'qty.numeric' => 'Quantity invalid.',

                    'price.required' => 'Price required.',
                    'price.numeric' => 'Price invalid.',

                    'total_price.required' => 'Total price required.',
                    'total_price.numeric' => 'Total price invalid.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $user = Auth::user();

            $data = Cart::where('user_id', $user->id)->where('service_id', $service_id)->first();
            if (empty($data)) throw new Exception('No data found', 404);

            $data->qty = $request->qty;
            $data->price = $request->price;
            $data->total_price = $request->total_price;
            $data->save();

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $service_id): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = Cart::where('user_id', $user->id)->where('service_id', $service_id)->first();

            if (empty($data)) throw new Exception('No data found', 404);
            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
