<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $credentials = ['email' => $email, 'password' => $password];
        $attempt = Auth::attempt($credentials);

        if(!$attempt){

            return response()->json([ 'status' => false, 'message' => "username or password is wrong"]);
        }
        
        $user = User::where('email', $email)->first();
        $token = $user->createToken('login')->accessToken;
        // dd($token);
        return response()->json([
            'access_token' => $token,
            'expires_in' =>  31536000,
            'refresh_token' => "",
            'token_type' => "Bearer",
        ]);
    }

    public function register(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        if(empty($name) || empty($email) || empty($password)){
            abort(400, 'Please fill all the fields');
        }

        // dd($username);
        // dd($password);
        // dd($passwordConfirm);
        // dd($type);
        $user = User::where('email', $email)->first();
        // dd($user);
        
        if($user){
            $userIsValid = Auth::once(['email'=>$email,'password' => $password]);
            if(! $userIsValid){

                abort(400, 'This email is already exist');
            }
            
        }else{
            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->role = 'customer';
            $user->password = Hash::make($password);
            $user->save();
        }
        
        $token = $user->createToken('login')->accessToken;

        return response()->json([
            'access_token' => $token,
            'expires_in' =>  31536000,
            'refresh_token' => "",
            'token_type' => "Bearer",
        ]);

        // $req = Http::post(env('APP_URL') . '/oauth/token', [
        //     "grant_type" => "password",
        //     "client_id" => 2,
        //     "client_secret" => "r36gKazDFlNNvPkPQuCWMua8uymGFHJ5S3hb0qVw",
        //     "username" => $username,
        //     "password" => $password
        // ]);
        
        // return $req->body();  
    }

    public function check(){
        if(Auth::check()){
            return response()->json(['message' => 'valid'], 200);
        }

        return response()->json(['message' => 'not valid'], 300);
    }
}
