<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\User;
use Exception;

trait ResponseUtilities
{

    protected function verifiedResponse(User $user){
        if(!$user->verified){

            $this->data['code'] = 402;
            $this->data['message'] = __('messages.non_verified');
            $this->sendVerificationCode($user->id);
            return false;

        }else{

            $this->data['code'] = 200;
            $this->data['message'] = __('messages.login_success');
            $token = $user->createToken('authToken')->accessToken;
            $user['token'] = $token;
            $this->data['data'] = $user;
            return true;
        }
    }

    protected function signupSuccessResponse($user){
        $this->data['code'] = 200;
        $this->data['message'] = __('messages.signup_success');
        $this->sendVerificationCode($user->id);
    }

    protected function socialSignupSuccessResponse($user){

        $this->data['code'] = 200;
        $this->data['message'] = __('messages.social_signup_success');
        $this->data['data'] = [
            "image"=>$user->image,
            "name" => $user->name,
            "email" => $user->email
        ];
    }
    
    protected function initErrorResponse(Exception $e){

        report($e);
        $traceArray = $e->getTrace();
        $exceptionsMessage = ['message'=>$e->getMessage()];
        array_unshift($traceArray, $exceptionsMessage);

        $this->data['code'] = 500;
        $this->data['message'] = __('messages.server_error');
        $this->data['data'] = $traceArray;
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