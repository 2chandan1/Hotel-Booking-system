<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller{
    public function register(Request $request){
        $validator= Validator::make($request->all(),[
            'name'=>'required| string|max:255',
            'email'=>'required| string|email|max:255',
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
    }

    public function login(Request $request){
        $validator= Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
    }
    public function logout(Request $request){
        $user=request->user()->currentAccessToken()->delete();
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
}