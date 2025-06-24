<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Gift;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = Gift::orderBy('id', 'desc')->paginate($perPage);

            if (empty($data)) throw new Exception('No data found', 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): JsonResponse
    {
        try {
            $data = Gift::where('id', $id)->first();
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
                    'name' => 'required|max:255',
                    'price' => 'required|numeric',
                    'details' => 'nullable',
                ],
                [
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    'price.required' => 'Price required.',
                    'price.numeric' => 'Price numeric.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = new Gift();
            $data->name = $request->name;
            $data->price = $request->price;
            $data->details = $request->details;
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_name = 'gift_image_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('gift-images'), $image_name);

                $data->image = 'gift-images/' . $image_name;
            }

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
                    'name' => 'required|max:255',
                    'price' => 'required|numeric',
                    'details' => 'nullable',
                ],
                [
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    'price.required' => 'Price required.',
                    'price.numeric' => 'Price numeric.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = Gift::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_name = 'gift_image_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('gift-images'), $image_name);

                unlink(public_path($data->image));
                $data->image = 'gift-images/' . $image_name;
            }

            $data->name = $request->name;
            $data->price = $request->price;
            $data->details = $request->details;
            $data->save();

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request, String $id): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'status' => 'required|boolean',
                ],
                [
                    'status.required' => 'Status required.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = Gift::find($id);
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
    public function destroy(String $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = Gift::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            if (file_exists(public_path($data->image))) unlink(public_path($data->image));
            $data->delete();

            DB::commit();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
