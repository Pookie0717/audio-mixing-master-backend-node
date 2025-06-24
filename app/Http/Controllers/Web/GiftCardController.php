<?php

namespace App\Http\Controllers\Web;

use Exception;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GiftCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        try {
            $user = Auth::user();
            $perPage = $request->query('per_page', 10);

            // Initialize the query using the Label model
            $query = UserWallet::orderBy('id', 'desc')->where('user_id',$user->id);

            // Execute the query with pagination
            $data = $query->paginate($perPage);

            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): JsonResponse
    {
        try {
            $data = UserWallet::where('id', $id)->first();
            if (empty($data)) throw new Exception('No data found', 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
