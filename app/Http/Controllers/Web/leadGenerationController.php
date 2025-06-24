<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeadGeneration;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\User\SubscriptionMail;

class leadGenerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = LeadGeneration::orderBy('id', 'desc')->paginate($perPage);

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
            $data = LeadGeneration::where('id', $id)->first();
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

            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|string|lowercase|email|max:255',
                ],
                [
                    'email.required' => 'Email required',
                    'email.email' => 'Invalid Email',
                    'email.max' => 'Email must be less than 255 characters.',
                    'email.unique' => 'Email already exists.',
                    'email.lowercase' => 'Email must be lowercase.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }

            // Check if email already exists
            $existingEmail = LeadGeneration::where('email', $request->email)->first();
            if ($existingEmail) {
                return response()->json(['message' => 'Already subscribed.'], 200);
            }

            $data = new LeadGeneration();
            $data->email = $request->email;
            $data->save();
            Mail::to($request->email)->send(new SubscriptionMail([
                    'email' => $request->email
                    
                ]));
            DB::commit();
            return response()->json(['message' => 'Successfully subscribe for 10% discount', 'add_email' => $data], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
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
            DB::beginTransaction();

            $data = LeadGeneration::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            $data->delete();

            DB::commit();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    public function exportLead(Request $request) {
        // Validate the date range input
        $validator = Validator::make(
            $request->all(),
            [
                'start_date' => 'required',
                'end_date' => 'required'
            ]
        );

        // If validation fails, throw an exception
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
        }

        // Extract and format the start and end dates
        $date_range = $request->date_range;
        $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date)->startOfDay();
        $end_date = Carbon::createFromFormat('d/m/Y', $request->end_date)->endOfDay();
        
        
        $users = LeadGeneration::whereBetween('created_at', [$start_date, $end_date])->get();

        // If no orders are found, return an error response
        if ($users->isEmpty()) {
            return response()->json(['error' => 'No Users found in the provided date range.'], 404);
        }

        // Generate the Excel file using the OrdersExport class
        $export = new UsersExport($users);

        // Define the filename based on the date range
        $filename = 'lead-report-' . $start_date->format('d-m-Y') . '-to-' . $end_date->format('d-m-Y') . '.xlsx';

        // Return the Excel file as a download
        return Excel::download($export, $filename);
    }
}
