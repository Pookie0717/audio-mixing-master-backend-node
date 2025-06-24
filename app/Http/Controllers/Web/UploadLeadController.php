<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UploadLeadGeneration;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UploadLeadController extends Controller
{

    public function index(Request $request):JsonResponse
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
                    'image_file' => 'nullable|array',
                    'image_file.*' => 'nullable|file',
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
                ],
                [
                    'image_url.required_without' => 'An image URL or file is required.',
                    'image_file.required_without' => 'An image URL or file is required.'
                ]
            );
        
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
        
            $image_paths = [];
            $file_type = null;
            if ($request->has('image_file')) {
                foreach ($request->file('image_file') as $image) {
                    $image_name = 'Upload-Lead-File' . time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('Upload-Lead-Files'), $image_name);
                    $image_paths[] = 'Upload-Lead-Files/' . $image_name; // Store file paths
                }
                $file_type = 1; // Set file type for uploaded files
            } else if ($request->has('image_url')) {
                $image_paths[] = $request->image_url;
                $file_type = 0;
            }
        
            $data = new UploadLeadGeneration();
            $data->name        = $request->name;
            $data->email       = $request->email;
            $data->arlist_name = $request->arlist_name;
            $data->tarck_title = $request->tarck_title;
            $data->image = json_encode($image_paths); // Storing either the URL or the path to the uploaded file
            $data->services    = $request->services;
            $data->reference   = $request->reference;
            $data->file_type   = $file_type; 
            $data->save();
        
            DB::commit();
            return response()->json(['message' => 'success', 'upload_leads' => $data], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
        
    }

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

    public function display(){
        return response()->json('helo');
    }
    
    public function downloadZip(Request $request, $id)
    {
    try {
        // 1. Retrieve data
        $data = UploadLeadGeneration::where('id', $id)->first();
        if (empty($data)) {
            throw new Exception('No data found', 404);
        }
        $files = json_decode($data->image,true);
        if (empty($files)) {
            throw new Exception('No File found', 404);
        }
        // 2. Set up temporary directory for ZIP file
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true); // Create directory if it doesn't exist
        }



        // 5. Initialize ZipArchive
        $zip = new ZipArchive();
        $zipFileName = 'download_' . time() . '.zip';
        $zipFilePath = $tempDir . '/' . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception('Cannot create ZIP file', 500);
        }


        // 7. Add audio files to the ZIP
        foreach ($files as $index => $audioFileName) {
        
            // Path to the audio file in 'public/speech-audios/'
            $audioFilePath = public_path($audioFileName);
        
            // Check if file exists before adding to ZIP
            if (file_exists($audioFilePath)) {
                // Use basename to get only the file name without the directory structure
                $newAudioFileName = ($index + 1) . '-response-' . basename($audioFileName); 
                $zip->addFile($audioFilePath, $newAudioFileName); // Add directly to the root of the ZIP
            } else {
                // Optionally log missing files and skip
                continue;
            }
        }

        // 8. Finalize and close the ZIP file
        $zip->close();

        // Check if the ZIP file was successfully created
        if (!file_exists($zipFilePath)) {
            throw new Exception('ZIP file not found after creation', 500);
        }

        // 9. Move the ZIP file to public directory
        $publicTempDir = public_path('temp');
        if (!file_exists($publicTempDir)) {
            mkdir($publicTempDir, 0755, true); // Create public temp directory if not exists
        }

        $publicZipPath = $publicTempDir . '/' . $zipFileName;
        if (!copy($zipFilePath, $publicZipPath)) {
            throw new Exception('Failed to move ZIP file to public directory', 500);
        }

        // 10. Serve the ZIP file for download
        $url = asset('temp/' . $zipFileName);
        return response()->json(['url' => $url]);

    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
    }
}


    public function downloadAudio(Request $request, $id)
{
    try {
        // Find the order item by id
        $data = UploadLeadGeneration::find($id);
        
        // Check if order item exists
        if (!$data) {
            throw new Exception('No order found', 404);
        }

        $file = $request->name; // Get the file name from the request

        // Decode the deliverable_files JSON to an array
        $files = json_decode($data->image, true);

        // Check if deliverable_files is an array
        if (!is_array($files) || empty($files)) {
            throw new Exception('No deliverable files found', 404);
        }

        // Match the exact file in the array
        if (!in_array($file, $files)) {
            throw new Exception('File not found in the deliverable files', 404);
        }

        // Assuming the file is directly in the public directory
        $publicPath = public_path($file); // Get the full path to the file

        // Check if the file exists on the server
        if (!file_exists($publicPath)) {
            throw new Exception('File does not exist on server', 404);
        }

        // Return the file as a download response
        return response()->download($publicPath, basename($file), [
            'Content-Type' => 'audio/mpeg', // Set appropriate content type for audio files
            'Content-Disposition' => 'attachment; filename="' . basename($file) . '"', // Force download
        ]);

    } catch (Exception $e) {
        // Return error as JSON response
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

}
