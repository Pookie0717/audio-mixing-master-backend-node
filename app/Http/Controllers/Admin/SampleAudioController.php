<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Sample;

class SampleAudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // try {
        //     $perPage = $request->query('per_page', 10);
        //     $data = Sample::orderBy('id', 'desc')->paginate($perPage);

        //     if (empty($data)) throw new Exception('No data found', 404);
        //     return response()->json($data, 200);
        // } catch (Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        // }
        try {
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
        
            // Initialize the query using the Label model
            $query = Sample::orderBy('id', 'desc');
        
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
    public function show(String $id): JsonResponse
    {
        try {
            $data = Sample::where('id', $id)->first();
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
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:255',
                    'before_audio' => 'required|file|mimetypes:audio/*',
                    'after_audio' => 'required|file|mimetypes:audio/*',
                ],
                [
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    'before_audio.required' => 'Before audio required.',
                    'before_audio.file' => 'Before audio must be a file.',
                    'before_audio.mimetypes' => 'Before audio must be a audio file.',

                    'after_audio.required' => 'After audio required.',
                    'after_audio.file' => 'After audio must be a file.',
                    'after_audio.mimetypes' => 'After audio must be a audio file.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = new Sample();
            $data->name = $request->name;

            if ($request->hasFile('before_audio')) {
                $before_audio = $request->file('before_audio');
                $before_audio_name = 'before_audio_' . time() . '.' . $before_audio->getClientOriginalExtension();
                $before_audio->move(public_path('sample-audios'), $before_audio_name);
                $data->before_audio = 'sample-audios/' . $before_audio_name;
            }

            if ($request->hasFile('after_audio')) {
                $after_audio = $request->file('after_audio');
                $after_audio_name = 'after_audio_' . time() . '.' . $after_audio->getClientOriginalExtension();
                $after_audio->move(public_path('sample-audios'), $after_audio_name);
                $data->after_audio = 'sample-audios/' . $after_audio_name;
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
    public function update(Request $request, String $id): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:255',
                    'before_audio' => 'nullable|file|mimetypes:audio/*',
                    'after_audio' => 'nullable|file|mimetypes:audio/*',
                ],
                [
                    'name.required' => 'Name required.',
                    'name.max' => 'Name maximum 255 characters.',

                    'before_audio.file' => 'Before audio must be a file.',
                    'before_audio.mimetypes' => 'Before audio must be a audio file.',

                    'after_audio.file' => 'After audio must be a file.',
                    'after_audio.mimetypes' => 'After audio must be a audio file.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = Sample::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->name = $request->name;
            $data->is_active = $request->is_active;

            if ($request->hasFile('before_audio')) {
                $before_audio = $request->file('before_audio');
                $before_audio_name = 'before_audio_' . time() . '.' . $before_audio->getClientOriginalExtension();
                $before_audio->move(public_path('sample-audios'), $before_audio_name);

                unlink(public_path($data->before_audio));
                $data->before_audio = 'sample-audios/' . $before_audio_name;
            }

            if ($request->hasFile('after_audio')) {
                $after_audio = $request->file('after_audio');
                $after_audio_name = 'after_audio_' . time() . '.' . $after_audio->getClientOriginalExtension();
                $after_audio->move(public_path('sample-audios'), $after_audio_name);

                unlink(public_path($data->after_audio));
                $data->after_audio = 'sample-audios/' . $after_audio_name;
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

            $data = Sample::find($id);
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
            $data = Sample::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            
            if (file_exists(public_path($data->before_audio))) unlink(public_path($data->before_audio));
            if (file_exists(public_path($data->after_audio))) unlink(public_path($data->after_audio));

            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
