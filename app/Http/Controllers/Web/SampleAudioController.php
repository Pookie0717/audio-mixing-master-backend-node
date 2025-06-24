<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Sample;

class SampleAudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = Sample::where('is_active', 1)->paginate($perPage);
            if (empty($data)) return response()->json(['error' => 'No data found.'], 404);
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
            $data = Sample::where('id', $id)->where('is_active', 1)->first();
            if (empty($data)) return response()->json(['error' => 'No data found.'], 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
