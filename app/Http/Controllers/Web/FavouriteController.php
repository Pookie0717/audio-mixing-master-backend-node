<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Favourite;
use Illuminate\Support\Facades\Auth;

class FavouriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // $perPage = $request->query('per_page', 10);
            $data = DB::table('favourites')
            ->join('services', 'favourites.service_id', '=', 'services.id')
            ->join('labels', 'services.label_id', '=', 'labels.id')
            ->where('favourites.user_id', $user->id)
            ->select('favourites.*', 'services.*', 'labels.name as label_name')
            ->orderBy('favourites.id', 'desc')
            ->paginate();

            if (empty($data)) throw new Exception('No data found', 404);
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
            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(),
                [
                    'service_id' => 'required|exists:services,id',
                ],
                [
                    'service_id.required' => 'Service required.',
                    'service_id.exists' => 'Service does not exists.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $user = Auth::user();

            $favourite = new Favourite();
            $favourite->user_id = $user->id;
            $favourite->service_id = $request->service_id;
            $favourite->save();

            // Fetch service details from the database
            $data = DB::table('favourites')
            ->join('services', 'favourites.service_id', '=', 'services.id')
            ->join('labels', 'services.label_id', '=', 'labels.id')
            ->where('favourites.id', $favourite->id)
            ->select('favourites.*', 'services.*', 'labels.name as label_name')
            ->first();

            DB::commit();
            return response()->json($data, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function FavouriteDestroy(): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // Use authenticated user's ID
            $userId = auth()->id();  // Assuming you are using Laravel's default authentication
            $serviceId = request()->service_id;
            
            // Delete the favorite directly if it exists
            $result = Favourite::where('user_id', $userId)
                               ->where('service_id', $serviceId)
                               ->delete();
            
            // Check if the delete operation affected any rows
            if ($result === 0) {
                throw new Exception('No data found', 404);
            }
            
            DB::commit();
            
            return response()->json('Deleted successfully', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
        
    
}
