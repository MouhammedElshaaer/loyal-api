<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\User;
use Exception;

trait ResponseUtilities
{

    protected function verifiedResponse(User $user){
        if(!$user->verified){

            $this->initResponse(402, 'non_verified');
            $this->sendVerificationCode($user->id);
            return false;

        }else{

            $token = $user->createToken('authToken')->accessToken;
            $user['token'] = $token;
            $this->initResponse(200, 'login_success', $user);
            return true;
        }
    }

    protected function signupSuccessResponse($user){
        $this->initResponse(200, 'signup_success');
        $this->sendVerificationCode($user->id);
    }

    protected function socialSignupSuccessResponse($user){
        $data = ["image"=>$user->image, "name" => $user->name, "email" => $user->email];
        $this->initResponse(201, 'social_signup_success', $data);
    }
    
    protected function initErrorResponse(Exception $e){

        report($e);
        $traceArray = $e->getTrace();
        $exceptionsMessage = ['message'=>$e->getMessage()];
        array_unshift($traceArray, $exceptionsMessage);
        
        $this->initResponse(500, 'server_error', $traceArray);
        
    }

    protected function initResponse($code, $messagesArrayKey, $data=null){

        $this->data['code'] = $code;
        $this->data['message'] = __('messages.'.$messagesArrayKey);
        $this->data['data'] = $data? $data: new \stdClass();
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
}