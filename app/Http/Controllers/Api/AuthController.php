<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller{
    public function register(Request $request){
        try {
        $validator= Validator::make($request->all(),[
            'name'=>'required| string|max:255',
             'email'    => 'required|string|email|max:255|unique:email', 
            'password'=>'required|string|min:8',
            'phone'=>'required|string|max:15',
            'role'=>'in:customer,hotel_manager,admin'
        ]);
        if($validator->fails()){
            return response()->json([
                'error'=>$validator->errors(),
                'message'=>'Validation Error',
            ],422);
            
        }
         if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'error'   => ['email' => ['The email has already been taken.']],
                'message' => 'Validation Error',
            ], 422);
        }
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=> Hash::make($request->password),
            'phone'=>$request->phone,
            'role'=>$request->role?? 'customer',
            'is_active'=>true,
        ]);
        
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'user'=>$user,
            'token'=>$token,
            'message'=>'User registered successfully',    
        ],201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while registering the user.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request){
         try {
        $validator= Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user= User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'message'=>'Invalid credentials'
            ],401);
        }
        IF($user->is_active === false){
            return response()->json([
                'message'=>'User is not active'
            ],403);
        }
        $token = $user->createToken('auth-token')->plainTextToken;
         return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'An error occurred while logging in.',
            'message' => $e->getMessage(),
        ], 500);
    }
    }
    public function logout(Request $request){
        $user=$request->user()->currentAccessToken()->delete();
        return response()->json([
            'message'=>'User logged out successfully',
            'user'=>$user
        ]);
    }
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
    public function allUsers()
    {
        $users = User::all();

        return response()->json([
            'count' => $users->count(),
            'users' => $users
        ], 200);
    }
    // Add this method to your AuthController class

public function updateUser(Request $request, $id)
{
    try {
        // Find the user
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
        ];
        
        // Add password validation if password is being updated
        if ($request->filled('password')) {
            $rules['current_password'] = 'required';
            $rules['password'] = 'required|confirmed|min:8';
        }
        
        // Validate the request
        $request->validate($rules);
        
        // If password is being updated, verify current password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'The current password is incorrect.',
                    'errors' => ['current_password' => ['The current password is incorrect.']]
                ], 422);
            }
        }
        
        // Update user data
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        
        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        // Save the user
        $user->save();
        
        // Return success response
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
        
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while updating the profile',
            'error' => $e->getMessage()
        ], 500);
    }
}
}