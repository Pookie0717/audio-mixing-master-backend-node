<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Service;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class ServiceTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            
            $data = [];

            $services = Service::where('is_active', 1)->where('parent_id',0)->get();
            
            
            if ($services->isEmpty()) throw new Exception('No data found.', 404);

            foreach ($services as $service) {
                $tags = explode(', ', $service->tags);
                $data = array_merge($data, $tags);
            }

            $uniqueData = array_unique($data);
            $tagCounts = array_count_values($data);

            $result = [
                [
                    'tag' => 'All',
                    'slug' => 'all',
                    'count' => count($services),
                ]
            ];
            foreach ($uniqueData as $tag) {
                $result[] = [
                    'tag' => $tag,
                    'slug' => strtolower(str_replace(' ', '-', $tag)),
                    'count' => $tagCounts[$tag],
                ];
            }

            return response()->json($result, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(String $tag, Request $request): JsonResponse
    {
    try {
        // Replace hyphens with spaces
        $tag = str_replace('-', ' ', $tag);

        // Set the default number of items per page
        $perPage = $request->input('per_page', 10);

        // Start building the query
        $query = DB::table('services')
    ->select(
        'services.id',
        'services.parent_id',
        'services.category_id',
        'services.image',
        'services.is_url',
        'services.name',
        'services.service_type',
        'services.price',
        'services.discounted_price',
        'services.label_id',
        'services.tags',
        'services.paypal_product_id',
        'services.paypal_plan_id',
        'services.stripe_plan_id',
        'services.stripe_product_id',
        'labels.name as label_name',
        'categories.name as category_name',
        'services.is_variation'
    )
    ->leftJoin('labels', function($join) {
        $join->on('services.label_id', '=', 'labels.id')
             ->where('labels.is_active', '=', 1);
    })
    ->leftJoin('categories', function($join) {
        $join->on('services.category_id', '=', 'categories.id')
             ->where('categories.is_active', '=', 1);
    })
    ->where('services.is_active', 1)->
            where('parent_id', 0);

        // If tag is not 'all', add the tag condition
        if ($tag !== 'all') {
            $query->whereRaw('LOWER(services.tags) LIKE ?', ['%' . strtolower($tag) . '%']);
        }

        // Paginate the results
        $services = $query->paginate($perPage);

        if ($services->isEmpty()) {
            return response()->json(['error' => 'No services found for the provided tag'], 404);
        }

        return response()->json($services, 200);

    } catch (Exception $e) {
        // Log the error message for debugging
        \Log::error('Error in show function: ' . $e->getMessage());

        // Return a 500 error if an exception occurred
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
