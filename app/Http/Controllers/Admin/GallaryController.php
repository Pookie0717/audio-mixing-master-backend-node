<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Gallary;

class GallaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)#: JsonResponse
    {
        // try {
        //     $perPage = $request->query('per_page', 10);
        //     $data = Gallary::orderBy('id', 'desc')->paginate($perPage);

        //     if (empty($data)) throw new Exception('No data found', 404);
        //     return response()->json($data, 200);
        // } catch (Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        // }
        try {
            // $perPage = $request->query('per_page', 10);
            // $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
        
            // Initialize the query using the Label model
            $query = Gallary::orderBy('id', 'desc')->get();
        
            // Check if 'is_active' parameter is present and modify the query accordingly
            // if ($isActive === 'active') {
            //     $query = $query->where('is_active', 1); // Filter for active labels
            // } elseif ($isActive === 'inactive') {
            //     $query = $query->where('is_active', 0); // Filter for inactive labels
            // }
        
            // Execute the query with pagination
            // $data = $query->paginate($perPage);
        
            // Check if data is empty and throw an exception if true
            if ($query->isEmpty()) {
                throw new Exception('No data found', 404);
            }
        
            return response()->json($query, 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)#: JsonResponse
    {
        try {
            $data = Gallary::where('id', $id)->first();
            if (empty($data)) throw new Exception('No data found', 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)#: JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'image' => 'required|file|mimetypes:image/*',
                ],
                [
                    'image.required' => 'Image required.',
                    'image.file' => 'Image must be a file.',
                    'image.mimetypes' => 'Image must be a audio file.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = new Gallary();

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_name = 'image_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('gallary-images'), $image_name);
                $data->image = 'gallary-images/' . $image_name;
            }

            $data->save();
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)#: JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'status' => 'required|boolean',
                ],
                [
                    'status.required' => 'Status required.',
                    'status.boolean' => 'Invalid status.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = Gallary::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->is_active = $request->status;
            $data->save();

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)#: JsonResponse
    {
        try {
            $data = Gallary::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            if (file_exists(public_path($data->image))) unlink(public_path($data->image));

            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
