<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserVerificationRequest;
use App\Http\Requests\ResendCodeRequest;
use App\Http\Requests\CompleteSignupRequest;
use App\Http\Requests\SocialLoginRequest;

use Exception;

use App\User;
use App\Models\LinkedSocialAccount;

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
                $this->verifiedResponse($user);

            }

        } catch (Exception $e) {

            report($e);
            $this->initErrorResponse($e);
        }

        return response()->json($this->data, 200);
    }

    public function socailLogin(SocialLoginRequest $request)
    {
        $provider =  $request->provider_name;
        $accessToken = $request->access_token;
        $providerUser = null;
        
        try {
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);

            if ($providerUser) {
                $user = $this->findOrCreate($providerUser, $provider);
            }else{
                $this->data['code'] = 401;
                $this->data['message'] = __('messages.login_fail');
            }

        } catch (Exception $e) {
            report($e);
            $this->initErrorResponse($e);
        }

        return response()->json($this->data, 200);
    }

    /**
     * Find or create user instance by provider user instance and provider name.
     * 
     * @param ProviderUser $providerUser
     * @param string $provider
     * 
     * @return User
     */
    public function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $provider)
                                                    ->where('provider_id', $providerUser->getId())
                                                    ->first();
                                                    
        if ($linkedSocialAccount) {
            $user = $linkedSocialAccount->user;

            if(!$user->phone){
                $this->socialSignupSuccessResponse($user);
            }else{
                $this->verifiedResponse($user);
            }

            return $user;

        } else {

            $user = null;
            if ($email = $providerUser->getEmail()) {
                $user = User::where('email', $email)->first();
            }
            if (! $user) {

                // Save user linked social account avatar
                // $file = $providerUser->getAvatar();
                // $path = "/users_avatar";
                // $avatarURL = $this->saveSocialAvatar($file, $path, $providerUser);

                $user = User::create([
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'image' => $providerUser->getAvatar(),
                ]);

                $this->socialSignupSuccessResponse($user);

            }else{
                $this->verifiedResponse($user);
            }
            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;

        }
    }

    public function register(CreateUserRequest $request){
    
        $attributes = $request->all();
        $attributes['password'] = bcrypt($attributes['password']);

        $this->data['code'] = 400;
        $this->data['message'] = __('messages.signup_fail');

        try {

            $user = User::create($attributes);
            if($user){$this->signupSuccessResponse($user);}
            else{throw new Exception;}

        } catch(Exception $e){$this->initErrorResponse($e);}

        return response()->json($this->data , 200);
    }

    public function completeSignup(CompleteSignupRequest $request){
        
        $accessToken = $request->access_token;
        $provider =  $request->provider_name;
        $attributes = $request->all();

        try {

            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
            if ($providerUser) {

                $user = $this->findOrCreate($providerUser, $provider);

                if(!$user){throw new Exception("User does not exist");}
                else{$user->update($attributes);}
                $this->signupSuccessResponse($user);

            }else{
                $this->data['code'] = 400;
                $this->data['message'] = __('messages.signup_fail');
            }

        }catch (Exception $e) {$this->initErrorResponse($e);}

        return response()->json($this->data , 200);
    }

    public function verify(UserVerificationRequest $request){

        try {

            $otp = $request->code; 
            $user = User::where("phone", $request->phone)
                        ->where("country_code", $request->country_code)
                        ->first();
            if($user){
                if(!$user->verified){
                    
                    if($otp == $user->otp){    

                        //Update the user instance in the DB
                        $user->verified = true;
                        $user->otp = null;
                        $user->save();

                        //Issuing Token
                        $token = $user->createToken('authToken')->accessToken;
                        $user['token'] = $token;

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

        return response()->json($this->data , 200);
    }

    public function resendCode(ResendCodeRequest $request){
        try{
            
            $user = User::where("phone", $request->phone)
                        ->where("country_code", $request->country_code)
                        ->first();

            if(!$user){
                $this->data['code'] = 400;
                $this->data['message'] = __('messages.resend_code_fail');
            }else{
                //Update the user instance in the DB
                $user->otp = null;
                $user->save();

                $this->sendVerificationCode($user->id);

                $this->data['code'] = 200;
                $this->data['message'] = __('messages.resend_code_success');
            }
            

        } catch (Exception $e) {

            report($e);
            $this->initErrorResponse($e);

        }

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

    private function generateOTP($len) {
        $result = '';
        for($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
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

    function saveSocialAvatar($fileURL, $path, $user){
        
        // $fileContents = file_get_contents($fileURL.hllo);
        // $name = 'user_avatar'.$user->getId();
        // return File::put(public_path() . $path . $user->getId() . ".jpg", $fileContents);
    }
    
}
