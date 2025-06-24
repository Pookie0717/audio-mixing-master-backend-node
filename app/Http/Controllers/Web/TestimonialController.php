<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $perPage = $request->query('per_page', 10);
            $data = Testimonial::where('user_id', $user->id)->orderBy('id', 'desc')->paginate($perPage);

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

            $data = new Testimonial();
            $data->user_id = $user->id;
            $data->service_id = $request->service_id;
            $data->save();

            DB::commit();
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
            DB::beginTransaction();

            $data = Testimonial::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            $data->delete();

            DB::commit();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    
    public function TestimonialList(): JsonResponse
    {
        try {
            $data = Testimonial::get();

            if ($data->isEmpty()) throw new Exception('No data found', 200);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
