<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{

    
    public function index(Request $request):JsonResponse{
        try{
            $query = Coupon::orderBy('id', 'desc');
            
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active');
        
            if ($isActive === 'active') {
                $query = $query->where('is_active', 1);
            } elseif ($isActive === 'inactive') {
                $query = $query->where('is_active', 0);
            }
        
            // Execute the query with pagination
            $coupons = $query->paginate($perPage);

            if (empty($coupons)) throw new Exception('No data found', 200);
            return response()->json($coupons,200);
        }
        catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    public function show(String $id):JsonResponse{
        try{
            $data = Coupon::where('id', $id)->first();
            if (empty($data)) throw new Exception('No data found', 200);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    public function store(Request $request):JsonResponse{
        try{
            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                [
                "code" => "required|alpha_num|max:50|unique:coupons,code",
                "discount_type" => "required|in:percentage,fixed",
                "discount_value" => "required|numeric",
                "max_uses" => "nullable|numeric",
                "uses" => "nullable|numeric",
                "start_date" => "required|date_format:Y-m-d",
                "end_date" => "nullable|date_format:Y-m-d|after:start_date",
                "is_active" => 'nullable|boolean',
                "coupon_type" =>"required|boolean",
                ],
                [
                    'code.unique' => 'The coupon code has already been taken.',
                ]
                );
                // return response()->json('test-pass');
                if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
                
                $data = new Coupon();
                
                $data->code = $request->code;
                $data->discount_type = $request->discount_type;
                $data->coupon_type = $request->coupon_type;
                $data->discount_value = $request->discount_value;
                $data->max_uses = $request->max_uses ?? null;
                $data->uses = 0;
                if($request->product_ids != null && $request->coupon_type == 1){
                    $productIds = json_encode($request->product_ids, true);
                    $data->product_ids = $productIds;
                }
                $data->start_date = $request->start_date;
                $data->end_date = $request->end_date ?? null;
                $data->is_active = $request->is_active ?? 0;
                $data->save();

                DB::commit();
                return response()->json(['message'=>"success"],200);
            } catch(Exception $e){

                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
            }
    }
    
    public function update(Request $request, String $id): JsonResponse
    {
    try {
        // Validation
        $validator = Validator::make(
            $request->all(),
            [
                "code" => [
                    'required',
                    'alpha_num',
                    'max:50',
                    Rule::unique('coupons', 'code')->ignore($id),
                ],
                "discount_type" => "required|in:percentage,fixed",
                "discount_value" => "required|numeric",
                "max_uses" => "nullable|numeric",
                "start_date" => "required|date_format:Y-m-d",
                "end_date" => "nullable|date_format:Y-m-d|after:start_date",
                "coupon_type" => "required|boolean"
                // "product_ids" => "required|array",
                // "product_ids.*" => "integer|exists:products,id",
            ],
            [
                'code.unique' => 'The coupon code has already been taken.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Find the Coupon
        $data = Coupon::find($id);
        if (empty($data)) {
            return response()->json(['error' => 'No data found'], 404);
        }

        // Update Coupon
        $data->code = $request->code;
        $data->discount_type = $request->discount_type;
        $data->discount_value = $request->discount_value;
        $data->max_uses = $request->max_uses ?? 0;
        if($request->product_ids != null && $request->coupon_type == 1){
            $productIds = json_encode($request->product_ids, true);
            $data->product_ids = $productIds;
        }
        $data->coupon_type = $request->coupon_type;
        $data->start_date = $request->start_date;
        $data->end_date = $request->end_date ?? null;
        $data->is_active = $request->is_active ?? 0;
        $data->save();

        return response()->json($data, 200);
    } catch (\Exception $e) {
        // Handle exception and ensure HTTP status code is valid
        $statusCode = $e->getCode();
        if ($statusCode < 100 || $statusCode >= 600) {
            $statusCode = 500; // Default to 500 if the code is not a valid HTTP status code
        }
        return response()->json(['error' => $e->getMessage()], $statusCode);
    }
}
    
    public function updateStatus(Request $request, String $id):JsonResponse{
        try{
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

            $data = Coupon::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->is_active = $request->status;
            $data->save();

            return response()->json($data, 200);
        } catch(Exception $e){
            return response()->json(['error'=> $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    public function destroy(String $id): JsonResponse
    {
        try {
            $data = Coupon::find($id);
            if (empty($data)) throw new Exception('No data found', 200);

            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
