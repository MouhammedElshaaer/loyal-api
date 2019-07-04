<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserVerificationRequest;
use App\Http\Requests\ResendCodeRequest;

use Exception;

use App\User;

class UsersController extends Controller
{
    private $data;

    public function __construct(){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

    }

    public function login(LoginRequest $request){

        $attributes = $request->all();

        try {
            
            if(!auth()->attempt($attributes)){
                $this->data['code'] = 401;
                $this->data['message'] = __('messages.login_fail');
            }else{

                $user = auth()->user();
            
                if(!$user->verified){

                    $this->data['code'] = 402;
                    $this->data['message'] = __('messages.non_verified');
                    $this->sendVerificationCode($user->id);

                }else{

                    $this->data['code'] = 200;
                    $this->data['message'] = __('messages.login_success');
                    $token = $user->createToken('authToken')->accessToken;
                    $user['token'] = $token;
                    $this->data['data'] = $user;

                }

            }

        } catch (Exception $e) {

            report($e);
            $this->initErrorResponse($e);
        }

        return response()->json($this->data, 200);
    }

    public function register(CreateUserRequest $request){

        /**
         * Another validation way
         */
        // $attributes = $request->all();
        // $messages =  __('validation.custom');
        // $messages['message'] = 'test';

        // $validator = Validator::make($attributes, $rules, $messages);
        // $validator->validate();
        // if($this->validator()->fails()){
        //     return "fails";
        // }

        $attributes = $request->all();
        dump($attributes);
        $attributes['password'] = bcrypt($attributes['password']);

        $this->data['code'] = 400;
        $this->data['message'] = __('messages.signup_fail');

        try {

            $user = User::create($attributes);
            $token = $user->createToken('authToken')->accessToken;
            $user['token'] = $token;

            if($user){
                $this->data['code'] = 200;
                $this->data['message'] = __('messages.signup_success');
                // $this->data['data'] = $user;
                $this->sendVerificationCode($user->id);
            }else{
                throw new Exception;
            }

        } catch (Exception $e) {
            report($e);
            $this->initErrorResponse($e);

        }

        return response()->json($this->data , 200);
    }

    public function verify(UserVerificationRequest $request){

        //Checking if authorized access
        // if(auth()->guard('api')->check()){

            try {

                $otp = $request->code; 
                $user = User::where("phone", $request->phone)
                            ->where("country_code", $request->country_code)
                            ->first();
                if($user){
                    // if(!auth()->guard('api')->user()->verified){
                    if(!$user->verified){
                        
                        // if($otp == auth()->guard('api')->user()->otp){    
                        if($otp == $user->otp){    

                            //Update the user instance in the DB
                            // $user = User::find(auth()->guard('api')->user()->id);
                            $user->verified = true;
                            $user->otp = null;
                            $user->save();

                            //Issuing Token
                            $token = $user->createToken('authToken')->accessToken;
                            $user['token'] = $token;

                            //Refreshing the cached user
                            // auth()->guard('api')->setUser($user);
                            $this->data['code'] = 200;
                            $this->data['message'] = __('messages.verification_success');
                            $this->data['data'] = $user;

                        }else{

                            $this->data['code'] = 400;
                            $this->data['message'] = __('messages.invalid_otp');

                        }
                    }else {

                        $this->data['code'] = 400;
                        $this->data['message'] = __('messages.already_verified');

                    }
                }else{

                    $this->data['code'] = 400;
                    $this->data['message'] = __('messages.resend_code_fail');

                }
            } catch (Exception $e) {

                report($e);
                $this->initErrorResponse($e);
    
            }
            
        // }else{

        //     $this->data['code'] = 400;
        //     $this->data['message'] = __('messages.unauthorized');

        // }

        return response()->json($this->data , 200);
    }

    public function resendCode(ResendCodeRequest $request){

        // if(auth()->guard('api')->check()){

            try{
                
                $user = User::where("phone", $request->phone)
                            ->where("country_code", $request->country_code)
                            ->first();

                if(!$user){
                    $this->data['code'] = 400;
                    $this->data['message'] = __('messages.resend_code_fail');
                }else{
                    //Update the user instance in the DB
                    // $user = User::find(auth()->guard('api')->user()->id);
                    // $user = auth()->user();
                    $user->otp = null;
                    $user->save();

                    //Refreshing the cached user
                    // auth()->guard('api')->setUser($user);
                    // auth()->setUser($user);
                    $this->sendVerificationCode($user->id);

                    $this->data['code'] = 200;
                    $this->data['message'] = __('messages.resend_code_success');
                }
                

            } catch (Exception $e) {

                report($e);
                $this->initErrorResponse($e);

            }
        // }else{

        //     $this->data['code'] = 400;
        //     $this->data['message'] = __('messages.unauthorized');

        // }

        return response()->json($this->data , 200);
    }


    public function sendVerificationCode($id){

        /**
         * TODO:
         * Actual implementation if sms verification code
         */
        
        // $otp = $this->generateOTP(4);
        $otp = "1234";
        //Update the user instance in the DB
        $user = User::find($id);
        $user->otp = $otp;
        $user->save();
        //Refreshing the cached user
        auth()->guard('api')->setUser($user);
    }

    private function generateOTP($len) {
        $result = '';
        for($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    protected function initErrorResponse(Exception $e){
        $traceArray = $e->getTrace();
        $exceptionsMessage = ['message'=>$e->getMessage()];
        array_unshift($traceArray, $exceptionsMessage);

        $this->data['code'] = 500;
        $this->data['message'] = __('messages.server_error');
        $this->data['data'] = $traceArray;
    }
    
}
