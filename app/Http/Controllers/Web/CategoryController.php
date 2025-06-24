<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = Category::select(
                'categories.id',
                'categories.name',
                'categories.is_active'
            )
            ->where('categories.is_active', 1)
                ->with([
                    'services' => function ($query) {
                        $query->select(
                            'id',
                            'category_id',
                            'image',
                            'name',
                            'service_type',
                            'price',
                            'discounted_price',
                            'label_id',
                            'paypal_product_id',
                            'stripe_plan_id',
                            'paypal_plan_id'
                        )
                            ->where('is_active', 1)->where('parent_id', 0);
                    },
                    'services.label' => function ($query) {
                        $query->select('labels.id', 'labels.name', 'labels.is_active')
                        ->where('labels.is_active', 1); // Filter labels to include only those that are active
                    },
                ])
                ->where('is_active', 1)
                ->get();

            if (empty($data)) return response()->json(['message' => 'No data found.'], 200);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): JsonResponse
    {
        try {
            $data = Category::where('id', $id)->where('is_active', 1)
                ->with([
                    'services' => function ($query) {
                        $query->select(
                            'id',
                            'category_id',
                            'image',
                            'name',
                            'service_type',
                            'price',
                            'discounted_price',
                            'label_id',
                            'paypal_product_id',
                            'paypal_plan_id',
                        )
                            ->where('is_active', 1);
                    },
                    'services.label' => function ($query) {
                        $query->select('labels.id', 'labels.name', 'labels.is_active')
                          ->where('labels.is_active', 1); // Filter labels to include only those that are active
                    },
                ])
                ->first();
            if (empty($data)) return response()->json(['message' => 'No data found.'], 200);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
