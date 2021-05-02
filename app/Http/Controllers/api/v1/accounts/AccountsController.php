<?php

namespace App\Http\Controllers\api\v1\accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Mail;

use App\Models\ResetPassword;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Validator;

class AccountsController extends Controller
{
    public static  function generateRandomString($length = 40) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * login API
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
    	$input = $request->all();
    	$validator = Validator::make($input, [
    		'email' => 'required|email',
    		'password' => 'required',
    	]);
    	if ($validator->fails()) {
    		
    		return response()->json($validator->errors(), 417);
    	}
    	$credentials = $request->only(['email', 'password']);
        config(['auth.guards.user.driver'=>'session']); 
        if(Auth::guard('user')->attempt(['email' => request('email'), 
        'password' => request('password')])){ 
			
			$user = Auth::guard('user')->user();
			$user['token'] = $user->createToken('access_token',['user'])->accessToken;

			return response()->json(['user' => $user], 200);
		}
		else {
			return response()->json(['error' => 'Unauthorized'], 401);
		}
    }
    /**
     * register API
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
    	$input = $request->all();
    	$validator = Validator::make($input, [
    		'name' => 'required',
			'email' => 'required|email',
			'password' => 'required',
			'c_password' => 'required|same:password',
    	]);
    	if ($validator->fails()) {
    		
    		return response()->json($validator->errors(), 417);
    	}
    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => bcrypt($request->password),
    	]);

    	$user['token'] = $user->createToken('access_token',['user'])->accessToken;

    	return response()->json(['user' => $user], 200);
    }

    /**
     * send password reset link request API
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request){

        $email = $request->input('email');
        $token = AccountsController::generateRandomString(40);

        ResetPassword::create([
            "email" => $email,
            "token" => $token,
        ]);

        $rootLink = env('APP_URL');
        $passResetLink = $rootLink."/password-reset"."/" .$token;
        $subject = "Password Reset Confirmation";
        Mail::to($email)->send(new ResetPasswordMail($passResetLink,$subject));

        if (Mail::failures()) {
            return response()->json(['status' => true,'message' => "failed to send email"], 200); 
        }

        return response()->json(['status' => true,'message' => "reset password email sent"], 200); 
    }

     /**
     * change password request API
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request){
        //$user_type = $request->input("user_type");
        $token = $request->input("token");
        $newPass = $request->input("password");

        $tokens = ResetPassword::where('token',$token)->get();

        try {
            $email = $tokens[0]->email;

            $user = User::where('email',$email)->update([
                "password" => bcrypt($newPass)
            ]
            );

            ResetPassword::where('token',$token)->delete();
            
            return response()->json(['status' => true,'message' => "passwords updated",'data'=>$email], 200); 
        } catch (\Throwable $th) {
            return response()->json(['status' => false,'message' => "failed to update password"], 200); 
        }
    }
}
