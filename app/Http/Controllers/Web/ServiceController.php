<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $type = $request->query('type');
            $category = $request->query('category');
        
            $data = Service::select(
                'id',
                'parent_id',
                'category_id',
                'image',
                'services.is_url',
                'name',
                'service_type',
                'price',
                'discounted_price',
                'label_id',
                'paypal_plan_id',
                'paypal_product_id',
                'stripe_plan_id',
                'stripe_product_id'
            )
            ->with([
                'label' => function ($query) {
                    $query->select('labels.id', 'labels.name', 'labels.is_active')
                          ->where('labels.is_active', 1); // Filter labels to include only those that are active
                },
                'category' => function ($query) {
                    $query->select(
                        'categories.id',
                        'categories.name',
                        'categories.is_active'
                    )
                    ->where('is_active', 1);
                },
            ])
            ->where('is_active', 1)
            ->orderBy('id', 'desc')->where('parent_id', 0);
        
            if ($category != null) $data = $data->where('category_id', $category);
            if ($type != null) $data = $data->where('service_type', $type);
        
            $data = $data->paginate($perPage);
        
            if ($data->isEmpty()) return response()->json(['error' => 'No data found.'], 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }

    /**
     * Display the specified resource.
     */
    // public function show(String $id): JsonResponse
    // {
    //     try {
    //         $data = Service::with([
    //             'label' => function ($query) {
    //                 $query->select('labels.id', 'labels.name', 'labels.is_active')
    //                       ->where('labels.is_active', 1); // Filter labels to include only those that are active
    //             },
    //             'category' => function ($query) {
    //                 $query->select(
    //                     'categories.id',
    //                     'categories.name',
    //                     'categories.is_active'
    //                 )
    //                     ->where('is_active', 1);
    //             },
    //         ])
    //             ->where('id', $id)
    //             ->where('is_active', 1)
    //             ->first();

    //         if (empty($data)) return response()->json(['error' => 'No data found.'], 404);
    //         return response()->json($data, 200);
    //     } catch (Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    
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
}
