<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;

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
                return response()->json($this->data, 200);
            }
    
            if(!auth()->user()->verified){
                $this->data['code'] = 402;
                $this->data['message'] = __('messages.non_verified');
                return response()->json($this->data , 200);
            }
        } catch (Exception $e) {
            report($e);
            
            $this->data['code'] = 500;
            $this->data['message'] = "Internal server error";
            // $this->data['data'] = $e->getTraceAsString();
            $this->data['data'] = $e;

            return response()->json($this->data , 200);
        }

        $token = auth()->user()->createToken('authToken')->accessToken;

        $user = auth()->user();
        $user['token'] = $token;

        $this->data['code'] = 200;
        $this->data['message'] = __('messages.login_success');
        $this->data['data'] = $user;

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
                $this->data['data'] = $user;
            }else{
                throw new Exception;
            }

        } catch (Exception $e) {
            report($e);
            $this->data['code'] = 500;
            $this->data['message'] = "Internal server error";
            $this->data['data'] = $e->getTraceAsString();
            return response()->json($this->data , 200);

        }

        return response()->json($this->data , 200);
    }

    public function verify(Request $request){
        if(Auth::check()){
            
        }
    }

    public function resendCode(Request $request){
        if(auth()->check()){
            $this->data['code'] = 200;
            $this->data['message'] = __('messages.resend_code_success');
            return response()->json($this->data , 200);
        }else{
            return "false";
        }
    }

}
