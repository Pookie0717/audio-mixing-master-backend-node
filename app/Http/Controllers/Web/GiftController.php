<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Gift;
use App\Models\UserHasGift;
use Illuminate\Support\Facades\Auth;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = Gift::where('is_active', 1)->orderBy('id', 'desc')->paginate($perPage);
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
            $data = Gift::where('id', $id)->first();
            if (empty($data)) return response()->json(['error' => 'No data found.'], 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
