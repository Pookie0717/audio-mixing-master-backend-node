<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\User\EmailVerificationMail;
use App\Mail\User\ResetPasswordMail;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
                    'phone_number' => 'required|string|max:255|unique:' . User::class,
                    'password' => 'required|string|min:8',
                    'confirm_password' => 'required|same:password',
                ],
                [
                    'first_name.required' => 'First name required',
                    'first_name.max' => 'First name must be less than 255 characters.',

                    'last_name.required' => 'Last name required',
                    'last_name.max' => 'Last name must be less than 255 characters.',

                    'email.required' => 'Email required',
                    'email.email' => 'Invalid Email',
                    'email.max' => 'Email must be less than 255 characters.',
                    'email.unique' => 'Email already exists.',
                    'email.lowercase' => 'Email must be lowercase.',

                    'phone_number.required' => 'Phone number required',
                    'phone_number.max' => 'Phone number must be less than 255 characters.',
                    'phone_number.unique' => 'Phone number already exists.',

                    'password.required' => 'Password required',
                    'password.min' => 'Password must be at least 8 characters.',

                    'confirm_password.required' => 'Confirm password required',
                    'confirm_password.same' => 'Confirm password does not match.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first(), 400);
            }
    
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ])->assignRole('user');
    
            // Find the admin role and then the first user who has that role
            $adminUser = User::role('admin')->firstOrFail(); // Fetching the first admin user
    
            // Insert the new user ID and admin ID into the chat table
            DB::table('chats')->insert([
                'senderId' => $user->id,
                'receiverId' => $adminUser->id // Using admin user ID as receiverId
            ]);
    
            $verificationUrl = route('auth.email-verification', [
                'id' => $user->getKey(),
                'hash' => Crypt::encryptString($user->email),
            ]);
    
            Mail::to($user)->send(new EmailVerificationMail([
                'name' => $user->first_name . ' ' . $user->last_name,
                'url' => $verificationUrl
            ]));
    
            return response()->json([
                'message' => 'Please verify your email to login.',
            ], 200);
        } catch (Exception $e) {
            $statusCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    // public function login(Request $request)
    // {
    //     try {
    //         $validator = validator(
    //             $request->all(),
    //             [
    //                 'email' => 'required|email|exists:users',
    //                 'password' => 'required',
    //                 'role' => 'nullable|in:user,administeration',
    //             ],
    //             [
    //                 'email.required' => 'Email Address required',
    //                 'email.email' => 'Invalid Email',
    //                 'email.exists' => 'Email Address does not exists',
        
    //                 'password.required' => 'Password required',
        
    //                 'role.required' => 'Role required',
    //                 'role.in' => 'Invalid role',
    //             ]
    //         );
        
    //         if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
        
    //         $user = User::where('email', $request->email)->first();
        
    //         if (User::where('email', $request->email)->whereNull('email_verified_at')->exists()) {
    //             throw new Exception('Please verify your email address first', 400);
    //         }
        
    //         // Check if the user is active
    //         if ($user->is_active == 0) {
    //             throw new Exception('User account is inactive', 403);
    //         }
        
    //         if ($request->role != null) {
    //             if ($request->role != "user") {
    //                 throw new Exception('Invalid Credentials', 200);
    //             }
        
    //             if ($request->role == "administeration") {
    //                 throw new Exception('Invalid Credentials', 200);
    //             }
    //         }
        
    //         if (!Hash::check($request->password, $user->password)) {
    //             throw new Exception('Incorrect email address or password', 400);
    //         }
        
    //         $roleName = $user->getRoleNames()[0];
    //         $role = Role::findByName($roleName);
    //         $permissions = $role->permissions->pluck('name');
        
    //         return response()->json([
    //             'token' => $user->createToken('authToken')->plainTextToken,
    //             'id' => $user->id,
    //             'role' => $roleName,
    //             'permissions' => $permissions,
    //         ], 200);
    //     } catch (Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
    //     }
        
    // }
    public function login(Request $request){
        try {
            $validator = validator(
                $request->all(),
                [
                    'email' => 'required|email|exists:users',
                    'password' => 'required',
                ],
                [
                    'email.required' => 'Email Address required',
                    'email.email' => 'Invalid Email',
                    'email.exists' => 'Email Address does not exists',
                ]
                );
                $user = User::where('email', $request->email)->first();
        
                if (User::where('email', $request->email)->whereNull('email_verified_at')->exists()) {
                    throw new Exception('Please verify your email address first', 400);
                }
                if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

                if($request->role == 'administeration'){
                    $user = User::where('email', $request->email)->first();
                    if (!Hash::check($request->password, $user->password)) {
                        throw new Exception('Incorrect email address or password', 400);
                    }
                    if($user){
                        if($user->role == 'admin' or $user->role == 'engineer'){
                            
                            $roleName = $user->getRoleNames()[0];
                            $role = Role::findByName($roleName);
                            $permissions = $role->permissions->pluck('name');
                            return response()->json([
                                'token' => $user->createToken('authToken')->plainTextToken,
                                'id' => $user->id,
                                'role' => $roleName,
                                'permissions' => $permissions,
                            ], 200);
                        }
                        else{
                            throw new Exception('Invalid Credentails', 400);
                        }
                    }
                }else if($request->role == 'user'){
                    $user = User::where('email', $request->email)->first();
                    if (!Hash::check($request->password, $user->password)) {
                        throw new Exception('Incorrect email address or password', 400);
                    }
                    
                    if($user->role == 'user'){
                        $roleName = $user->getRoleNames()[0];
                        $role = Role::findByName($roleName);
                        $permissions = $role->permissions->pluck('name');
                        return response()->json([
                            'token' => $user->createToken('authToken')->plainTextToken,
                            'id' => $user->id,
                            'role' => $roleName,
                            'permissions' => $permissions,
                        ], 200);
                    }
                    else{
                            throw new Exception('Invalid Credentails', 400);
                        }
                }

        }catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function emailVerify(String $id, String $hash)
    {
        try {
            $email = Crypt::decryptString($hash);
            $user = User::where('id', $id)->where('email', $email)->first();

            if ($user) {
                $user->update(['email_verified_at' => now()]);
                // return redirect(env('FRONTEND_URL') . '/login?verified=1');
                return redirect('https://zetdigitesting.online/login?verified=1');
                
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function forgetPassword(Request $request)
    {
        try {
            $validator = validator(
                $request->all(),
                [
                    'email' => 'required|email|exists:users',
                ],
                [
                    'email.required' => 'Email Address required',
                    'email.email' => 'Invalid Email',
                    'email.exists' => 'Invalid Email Address',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $tokenExist = PasswordResetToken::where('email', $request->email)->exists();
            if ($tokenExist) PasswordResetToken::where('email', $request->email)->delete();

            $token = Str::random(60);
            PasswordResetToken::insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]);

            $user = User::where('email', $request->email)->first();

            Mail::to($request->email)->send(new ResetPasswordMail([
                'name' => $user->first_name . ' ' . $user->last_name,
                'url' => 'https://check.zetdigi.com/reset-password/'.$request->email.'/'.$token,
                 
            ]));

            return response()->json([
                'message' => 'Reset link sent successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function resetPassword(Request $request, $email, $token)
    {
        try {
            $validator = validator(
                $request->all(),
                [
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                ],
                [
                    'password.required' => 'Password required',
                    'password.min' => 'Password must be at least 8 characters.',

                    'confirm_password.required' => 'Confirm password required',
                    'confirm_password.same' => 'Confirm password does not match.',
                ]
            );

            $user = User::where('email', $email)->first();
            if (!$user) throw new Exception('Invalid email address', 400);

            $tokenExist = PasswordResetToken::where('email', $email)->where('token', $token)->exists();
            if (!$tokenExist) throw new Exception('Invalid token', 400);

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            if (User::where('email', $request->email)->whereNull('email_verified_at')->exists()) throw new Exception('Please verify your email address first');

            $user->update(['password' => Hash::make($request->password)]);

            return response()->json([
                'message' => 'Password reset successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
