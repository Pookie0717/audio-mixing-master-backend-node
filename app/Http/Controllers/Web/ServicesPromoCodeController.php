<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ServicesPromoCode; // Check this namespace
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;;

class ServicesPromoCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $data = ServicesPromoCode::orderBy('id', 'desc')->paginate($perPage);

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
            $data = ServicesPromoCode::with('ServicesDetails')
                ->where('id', $id)
                ->first();
            if (empty($data)) throw new Exception('No data found', 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function insertServicePromoCodes(Request $request)
    {
        DB::beginTransaction();

        try {
            // Logging the request data to debug the input
            Log::info('Request Data:', $request->all());

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|array|min:1',
                'service_id.*' => 'required|integer|exists:services,id',  // Ensures each element is a valid integer and exists in the services table

                'promo_code' => 'required|string|max:255|unique:services_promo_codes,promo_code', // Ensuring unique promo codes
                'promo_code_balance' => 'required|numeric', // Changed to numeric to ensure only numbers are accepted
                'expiryDate' => 'required|date',  // Ensure the expiry is a valid date
                // 'time_limit' => 'required|numeric', // Changed to numeric to ensure only numbers are accepted
                'status' => 'required|string|max:255'  // Validate status as a string
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            // Process each service ID
            foreach ($request->service_id as $id) {
                $data = new ServicesPromoCode();
                $data->service_id = $id;
                $data->promo_code = $request->promo_code;
                $data->promo_code_balance = $request->promo_code_balance;
                $data->expiryDate = $request->expiryDate;
                $data->time_limit = 1;
                $data->status = $request->status;
                $data->save();
            }

            // Commit the transaction
            DB::commit();
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            // Roll back the transaction in case of exception
            DB::rollBack();
            Log::error('Database Error:', ['error' => $e->getMessage()]);

            $status = $e->getCode();
            // Ensure the status code is a valid HTTP status code
            if (!is_int($status) || $status < 100 || $status > 599) {
                $status = 500; // Default to 500 Internal Server Error
            }

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }


    //Promo Code verify
    public function verifyPromoCodes(String $code): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Check the promo code in services_promo_codes table
            $promo = DB::table('services_promo_codes')
                ->where('promo_code', $code)
                ->lockForUpdate() // Lock the row for an atomic operation
                ->first();

            // Check if the promo code exists and if it has uses left
            if (empty($promo)) {
                return response()->json(['error' => 'No such promo code found.'], 404);
            } elseif ($promo->time_limit <= 0) {
                return response()->json(['error' => 'Promo code cannot be used anymore.'], 400);
            }

            // Check if the promo code has expired
            $today = date('Y-m-d');
            if ($promo->expiryDate <= $today) {
                return response()->json(['error' => 'Promo code has expired.'], 400);
            }

            // Decrement the use limit of the promo code
            DB::table('services_promo_codes')
                ->where('promo_code', $code)
                ->decrement('time_limit');

            DB::commit();  // Commit the transaction

            return response()->json(['message' => 'Promo code verified successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction on error
            return response()->json(['error' => $e->getMessage()], 500);
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
        try {
            // Logging the request data to debug the input
            Log::info('Request Data:', $request->all());

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|array|min:1',
                'service_id.*' => 'required|integer|exists:services,id',  // Ensures each element is a valid integer and exists in the services table

                'promo_code' => 'required|string|max:255|unique:services_promo_codes,promo_code', // Ensuring unique promo codes
                'promo_code_balance' => 'required|numeric', // Changed to numeric to ensure only numbers are accepted
                'expiryDate' => 'required|date',  // Ensure the expiry is a valid date
                // 'time_limit' => 'required|numeric', // Changed to numeric to ensure only numbers are accepted
                'status' => 'required|string|max:255'  // Validate status as a string
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['error' => $validator->errors()->first()], 400);
            }
            $data = ServicesPromoCode::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            // Process each service ID
            foreach ($request->service_id as $id) {
                $data = new ServicesPromoCode();
                $data->service_id = $id;
                $data->promo_code = $request->promo_code;
                $data->promo_code_balance = $request->promo_code_balance;
                $data->expiryDate = $request->expiryDate;
                $data->time_limit = 1;
                $data->status = $request->status;
                $data->save();
            }

            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            // Roll back the transaction in case of exception
            DB::rollBack();
            Log::error('Database Error:', ['error' => $e->getMessage()]);

            $status = $e->getCode();
            // Ensure the status code is a valid HTTP status code
            if (!is_int($status) || $status < 100 || $status > 599) {
                $status = 500; // Default to 500 Internal Server Error
            }

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $data = ServicesPromoCode::find($id);
            if (empty($data)) throw new Exception('No data found', 404);
            $data->delete();

            DB::commit();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
