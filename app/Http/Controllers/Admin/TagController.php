<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\tag;
use Exception;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
        
            // Initialize the query using the Label model
            $query = tag::orderBy('id', 'desc');
        
            // Check if 'is_active' parameter is present and modify the query accordingly
            if ($isActive === 'active') {
                $query = $query->where('is_active', 1); // Filter for active labels
            } elseif ($isActive === 'inactive') {
                $query = $query->where('is_active', 0); // Filter for inactive labels
            }
        
            // Execute the query with pagination
            $data = $query->paginate($perPage);
        
            // Check if data is empty and throw an exception if true
            if ($data->isEmpty()) {
                return response()->json($data, 200);
            }
        
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $data = tag::where('id', $id)->first();
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
                    'tag_name' => 'required|max:255',
                ],
                [
                    'tag_name.required' => 'Tag Name required.',
                    'tag_name.max' => 'Tag Name maximum 255 characters.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = new tag();
            $data->tag_name = $request->tag_name;
            $data->save();

            DB::commit();
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

     /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'tag_name' => 'required|max:255',
                ],
                [
                    'tag_name.required' => 'Tag Name required.',
                    'tag_name.max' => 'Tag Name maximum 255 characters.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = tag::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->tag_name = $request->tag_name;
            $data->is_active = $request->is_active;
            // $data->is_active = 1;
            $data->save();

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = tag::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            $data->delete();

            DB::commit();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
