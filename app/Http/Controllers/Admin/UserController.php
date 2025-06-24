<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
        
            // Initialize the query using the User model and exclude the logged-in user and specific roles
            $query = User::where('id','!=', auth()->user()->id)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('role', ['admin', 'Engineer']); // Exclude users with 'admin' or 'Engineer' roles
                })
                ->orderBy('id', 'desc');
        
            // Check if 'is_active' parameter is present and modify the query accordingly
            if ($isActive === 'active') {
                $query = $query->where('is_active', 1); // Filter for active users
            } elseif ($isActive === 'inactive') {
                $query = $query->where('is_active', 0); // Filter for inactive users
            }
        
            // Execute the query with pagination
            $data = $query->paginate($perPage);
        
            // Check if data is empty and throw an exception if true
            if ($data->isEmpty()) {
                // throw new Exception('No data found', 404);
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
            $data = User::where('id', $id)->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            })->first();
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
                    'first_name' => 'required|max:255',
                    'last_name' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users',
                    'phone_number' => 'required|numeric|unique:users',
                    'password' => 'required|min:8|max:20',
                    'confirm_password' => 'required|same:password',
                ],
                [
                    'first_name.required' => 'First name required.',
                    'first_name.max' => 'First name must be less then 255 characters.',

                    'last_name.required' => 'Last name required.',
                    'last_name.max' => 'Last name must be less then 255 characters.',

                    'email.required' => 'Email required.',
                    'email.email' => 'Invalid email.',
                    'email.max' => 'Email must be less then 255 characters.',
                    'email.unique' => 'Email already exists.',

                    'phone_number.required' => 'Phone number required.',
                    'phone_number.numeric' => 'Invalid phone number.',
                    'phone_number.unique' => 'Phone number already exists.',

                    'password.required' => 'Password required.',
                    'password.min' => 'Password must be at least 8 characters.',
                    'password.max' => 'Password must be less then 20 characters.',

                    'confirm_password.required' => 'Confirm password required.',
                    'confirm_password.same' => 'Confirm password must be same as password.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ])->assignRole('user');

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
                    'first_name' => 'required|max:255',
                    'last_name' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users,' . $id,
                    'phone_number' => 'required|numeric|unique:users,' . $id,
                    'password' => 'nullable|min:8|max:20',
                    'confirm_password' => 'required|same:password',
                ],
                [
                    'first_name.required' => 'First name required.',
                    'first_name.max' => 'First name must be less then 255 characters.',

                    'last_name.required' => 'Last name required.',
                    'last_name.max' => 'Last name must be less then 255 characters.',

                    'email.required' => 'Email required.',
                    'email.email' => 'Invalid email.',
                    'email.max' => 'Email must be less then 255 characters.',
                    'email.unique' => 'Email already exists.',

                    'phone_number.required' => 'Phone number required.',
                    'phone_number.numeric' => 'Invalid phone number.',
                    'phone_number.unique' => 'Phone number already exists.',

                    'password.required' => 'Password required.',
                    'password.min' => 'Password must be at least 8 characters.',
                    'password.max' => 'Password must be less then 20 characters.',

                    'confirm_password.required' => 'Confirm password required.',
                    'confirm_password.same' => 'Confirm password must be same as password.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = User::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->first_name = $request->first_name;
            $data->last_name = $request->last_name;
            $data->email = $request->email;
            $data->phone_number = $request->phone_number;
            if (!empty($request->password)) $data->password = Hash::make($request->password);
            $data->save();

            $data->syncRoles(['user']);

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

            $data = User::find($id);
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
            $data = User::find($id);
            if (empty($data)) throw new Exception('No data found', 404);

            $data->delete();

            return response()->json('Deleted', 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    
    public function storeEngineer(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|max:255',
                    'last_name' => 'required|max:255',
                    'email' => 'required|email|max:255|unique:users',
                    'phone_number' => 'required|numeric|unique:users',
                    'password' => 'required|min:8|max:20',
                    'confirm_password' => 'required|same:password',
                ],
                [
                    'first_name.required' => 'First name required.',
                    'first_name.max' => 'First name must be less then 255 characters.',

                    'last_name.required' => 'Last name required.',
                    'last_name.max' => 'Last name must be less then 255 characters.',

                    'email.required' => 'Email required.',
                    'email.email' => 'Invalid email.',
                    'email.max' => 'Email must be less then 255 characters.',
                    'email.unique' => 'Email already exists.',

                    'phone_number.required' => 'Phone number required.',
                    'phone_number.numeric' => 'Invalid phone number.',
                    'phone_number.unique' => 'Phone number already exists.',

                    'password.required' => 'Password required.',
                    'password.min' => 'Password must be at least 8 characters.',
                    'password.max' => 'Password must be less then 20 characters.',

                    'confirm_password.required' => 'Confirm password required.',
                    'confirm_password.same' => 'Confirm password must be same as password.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'role'=> 'engineer',
                'email_verified_at'=> now(),
            ])->assignRole('engineer');

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
    
    public function listEngineer(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $isActive = $request->query('is_active'); // Get the 'is_active' parameter from the request
        
            // Initialize the query using the User model and exclude the logged-in user and specific roles
            $query = User::where('id','!=', auth()->user()->id)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('role', ['user', 'admin']); // Exclude users with 'admin' or 'Engineer' roles
                })
                ->orderBy('id', 'desc');
        
            // Check if 'is_active' parameter is present and modify the query accordingly
            if ($isActive === 'active') {
                $query = $query->where('is_active', 1); // Filter for active users
            } elseif ($isActive === 'inactive') {
                $query = $query->where('is_active', 0); // Filter for inactive users
            }
        
            // Execute the query with pagination
            $data = $query->paginate($perPage);
        
            // Check if data is empty and throw an exception if true
            if ($data->isEmpty()) {
                // throw new Exception('No data found', 404);
            }
        
            return response()->json($data, 200);
        } catch (Exception $e) {
            // Return an error response if an exception occurs
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }       
    }
    
    public function showEngineer(String $id): JsonResponse
    {
        try {
            $data = User::where('id', $id)->where('role','engineer')->first();
            if (empty($data)) throw new Exception('No data found', 404);

            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
}
