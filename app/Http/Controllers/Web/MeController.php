<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MeController extends Controller
{
    public $avatarDir = 'user-avatar/';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $data = User::where('id', $user->id)->first();
        if (empty($data)) return response()->json(['error' => 'No data found.'], 404);

        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|max:255',
                    'last_name' => 'required|max:255',
                    'user_name' => 'required|max:255|unique:users,user_name,' . Auth::user()->id,
                    'email' => 'required|email|max:255|unique:users,email,' . Auth::user()->id,
                ],
                [
                    'first_name.required' => 'First name required.',
                    'first_name.max' => 'First name maximum 255 characters.',

                    'last_name.required' => 'Last name required.',
                    'last_name.max' => 'Last name maximum 255 characters.',

                    'user_name.required' => 'User name required.',
                    'user_name.max' => 'User name maximum 255 characters.',
                    'user_name.unique' => 'User name already exists.',

                    'email.required' => 'Email required.',
                    'email.email' => 'Invalid Email.',
                    'email.max' => 'Email must be less than 255 characters.',
                    'email.unique' => 'Email already exists.',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $data = User::find(Auth::user()->id);
            if (empty($data)) return response()->json(['error' => 'No data found.'], 404);

            $data->first_name = $request->first_name;
            $data->last_name = $request->last_name;
            $data->user_name = $request->user_name;
            $data->email = $request->email;
            if (!empty($request->phone_number)) $data->phone_number = $request->phone_number;
            if (!empty($request->password)) $data->password = Hash::make($request->password);

            $data->save();
            
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
