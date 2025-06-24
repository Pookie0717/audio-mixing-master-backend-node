<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UploadLeadGeneration;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UploadLeadGenerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = UploadLeadGeneration::orderBy('id', 'desc')->paginate($perPage);

            if (empty($data)) throw new Exception('No data found', 404);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = UploadLeadGeneration::where('id', $id)->first();
            if (empty($data)) throw new Exception('No data found', 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate that at least one of image_url or image_file must be provided
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:255',
                    'email' => 'required|string|email|max:255',
                    'arlist_name' => 'required|max:255',
                    'tarck_title' => 'required|max:255',
                    'image_url' => 'nullable|url',
                    'image_file' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,audio/mpeg,audio/mp3,audio/wav',
                    'services' => 'required|max:255',
                    'reference' => 'required|max:255',
                ],
                [
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    'email.required' => 'Email required',
                    'email.email' => 'Invalid Email',
                    'email.max' => 'Email must be less than 255 characters.',

                    'arlist_name.required' => 'Arlist Name required.',
                    'arlist_name.max' => 'Arlist Name maximum 255 characters.',

                    'tarck_title.required' => 'Tarck Title required.',
                    'tarck_title.max' => 'Tarck Title maximum 255 characters.',

                    'image_url.url' => 'Invalid URL format.',
                    'image_file.mimetypes' => 'Invalid image format.',
                ],
                [
                    'image_url.required_without' => 'An image URL or file is required.',
                    'image_file.required_without' => 'An image URL or file is required.'
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }

            $image_path = null;
            if ($request->has('image_file')) {
                $image = $request->file('image_file');
                $image_name = 'Upload-Lead-File' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('Upload-Lead-Files'), $image_name);
                $image_path = 'Upload-Lead-Files/' . $image_name;
            } elseif ($request->has('image_url')) {
                $image_path = $request->image_url;
            }

            $data = new UploadLeadGeneration();
            $data->name        = $request->name;
            $data->email       = $request->email;
            $data->arlist_name = $request->arlist_name;
            $data->tarck_title = $request->tarck_title;
            $data->image       = $image_path; // Storing either the URL or the path to the uploaded file
            $data->services    = $request->services;
            $data->reference   = $request->reference;
            $data->save();

            DB::commit();
            return response()->json(['message' => 'success', 'upload_leads' => $data], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = UploadLeadGeneration::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            if (file_exists(public_path($data->image))) unlink(public_path($data->image));

            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
