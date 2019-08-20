<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\User;
use Exception;

trait ResponseUtilities
{

    protected function verifiedResponse(User $user){
        if (!$user->verified) {

            $this->initResponse(402, 'non_verified');
            $this->sendVerificationCode(User::class, $user->id, '1234');
            return false;

        } else {

            $token = $user->createToken('authToken')->accessToken;
            $user['token'] = $token;
            $this->initResponse(200, 'login_success', $user);
            return true;
        }
    }

    protected function signupSuccessResponse($user){
        $this->initResponse(200, 'signup_success');
        $this->sendVerificationCode(User::class, $user->id, '1234');
    }

    protected function socialSignupSuccessResponse($user){
        $data = ["image"=>$user->image, "name" => $user->name, "email" => $user->email];
        $this->initResponse(201, 'social_signup_success', $data);
    }

    protected function initErrorResponse(Exception $e){

        report($e);
        $error = [
            'message' => $e->getMessage(),
            'trace' => $e->getTrace()
        ];

        $this->initResponse(500, 'server_error');
        $this->data['error'] = $error;

    }

    protected function initResponse($code, $messagesArrayKey, $data=null, $params=[]){

        $this->data['code'] = $code;
        $this->data['message'] = __('messages.'.$messagesArrayKey, $params);
        $this->data['data'] = $data? $data: new \stdClass();
    }

    public function sendVerificationCode($dataTypePath, $dataRowId, $code){

        /**
         * TODO:
         * Actual implementation of sms verification code
         */

        // $otp = $this->generateOTP(4);
        $otp = $code;
        //Update the dataRow instance in the DB
        $dataRow = ($dataTypePath)::find($dataRowId);
        $dataRow->otp = $otp;
        $dataRow->save();

        if($dataTypePath == User::class){
            //Refreshing the cached user
            auth()->guard('api')->setUser($dataRow);
        }
    }
}
