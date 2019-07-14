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
use App\Http\Requests\VerifyPhoneRequest;
use App\Http\Requests\ValidateUserRequest;
use App\Http\Requests\ResetPasswordRequest;

use Exception;
use Google_Client;

use App\Http\Traits\ResponseUtilities;

use App\User;
use App\Models\LinkedSocialAccount;

class UsersController extends Controller
{
    use ResponseUtilities;

    private $data;

    public function __construct(){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

    }

    public function login(LoginRequest $request){

        $attributes = $request->only('country_code', 'phone', 'password');

        try {
            
            if(!auth()->attempt($attributes)){
                $this->initResponse(401, 'login_fail');
            }else{
                $user = auth()->user();
                $this->verifiedResponse($user);

            }

        } catch (Exception $e) {$this->initErrorResponse($e);}

        return response()->json($this->data, 200);
    }

    public function socailLogin(SocialLoginRequest $request)
    {
        $provider =  $request->provider_name;
        $socialToken = $request->social_token;
        $providerUser = null;
        
        try {

            $providerUser = $this->socialite($provider, $socialToken);
            if ($providerUser) {

                if($providerUser->getEmail()){$user = $this->findOrCreate($providerUser, $provider);}
                else{$this->initResponse(400, 'missing_associated_email');}

            }else{$this->initResponse(400, 'social_signup_fail');}

        } catch (Exception $e) {$this->initErrorResponse($e);}

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
            if (!$user) {

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
        $this->initResponse(400, 'signup_fail');

        try {

            $user = User::create($attributes);
            if($user){$this->signupSuccessResponse($user);}
            else{throw new Exception;}

        } catch(Exception $e){$this->initErrorResponse($e);}

        return response()->json($this->data , 200);
    }

    public function completeSignup(CompleteSignupRequest $request){
        
        $socialToken = $request->social_token;
        $provider =  $request->provider_name;
        $attributes = $request->only('country_code', 'phone', 'name');

        try {

            $providerUser = $this->socialite($provider, $socialToken);
            if ($providerUser) {

                $user = $this->findOrCreate($providerUser, $provider);

                if(!$user){throw new Exception("User does not exist");}
                else{$user->update($attributes);}
                $this->signupSuccessResponse($user);

            }else{$this->initResponse(400, 'signup_fail');}

        }catch (Exception $e) {$this->initErrorResponse($e);}

        return response()->json($this->data , 200);
    }

    public function verifyAccount(UserVerificationRequest $request){

        try {

            $user = null;
            $otp = $request->code; 

            if($request->has('phone')){

                $user = User::where("phone", $request->phone)
                            ->where("country_code", $request->country_code)
                            ->first();

            }else if($request->has('social_token')){

                $socialToken = $request->social_token;
                $provider =  $request->provider_name;
                $providerUser = $this->socialite($provider, $socialToken);
                
                if ($providerUser) {

                    if($providerUser->getEmail()){$user = $this->findOrCreate($providerUser, $provider);}
                    else{$this->initResponse(400, 'missing_associated_email');}

                }
            }

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
                        $this->initResponse(200, 'verification_success', $user);

                    }else{$this->initResponse(400, 'invalid_otp');}
                }else {$this->initResponse(400, 'already_verified');}
            }else{$this->initResponse(400, 'user_validation_fail');}
        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data , 200);
    }

    public function validateUser(ValidateUserRequest $request){

        try {
            $attributes = $request->only('country_code', 'phone');
            $user = User::where("phone", $request->phone)->where("country_code", $request->country_code)->first();
            if($user){
                $this->initResponse(200, 'user_validation_success');
                $this->sendVerificationCode($user->id);
            }else{$this->initResponse(400, 'user_validation_fail');}
        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data , 200);
    }

    public function verifyPhone(VerifyPhoneRequest $request){

        try {
            $attributes = $request->only('country_code', 'phone', 'code');
            $otp = $attributes['code'];
            $user = User::where("phone", $request->phone)->where("country_code", $request->country_code)->first();
            if($user){
                if($otp == $user->otp){

                    //Issuing Token
                    $token = $user->createToken('authToken')->accessToken;
                    $user['token'] = $token;
                    $this->initResponse(200, 'phone_verification_success', $user);

                }else{$this->initResponse(400, 'invalid_otp');}
            }else{$this->initResponse(400, 'user_validation_fail');}
        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data , 200);
    }

    public function resetPassword(ResetPasswordRequest $request){

        try{
            $attributes = $request->only('password', 'code');
            if(auth()->guard('api')->check() && auth()->guard('api')->user()->otp == $attributes['code']){

                $user = User::where('id', auth()->guard('api')->user()->id)->first();
                if($user){
                    $user->password = bcrypt($attributes['password']);
                    $user->otp = null;
                    $user->save();
                }else{throw new Exception('User not found');}

                auth()->guard('api')->setUser($user);
                $this->initResponse(200, 'password_reset_success');

            }else{$this->initResponse(400, 'unauthorized');}
        } catch(Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data , 200);
    }

    public function resendCode(ResendCodeRequest $request){

        try{
            $user = User::where("phone", $request->phone)->where("country_code", $request->country_code)->first();
            if(!$user){$this->initResponse(400, 'resend_code_fail');}
            else{
                //Update the user instance in the DB
                $user->otp = null;
                $user->save();

                $this->sendVerificationCode($user->id);
                $this->initResponse(200, 'resend_code_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data , 200);
    }

    public function logout(Request $request){
        
        if(auth()->guard('api')->check()){

            $accessToken = auth()->guard('api')->user()->token();
            \DB::table('oauth_refresh_tokens')->where('access_token_id', $accessToken->id)->update(['revoked' => true]);
            $accessToken->revoke();
            $this->initResponse(200, 'logout_success');

        }else{$this->initResponse(400, 'unauthorized');}
        return response()->json($this->data , 200);
    }

    protected function generateOTP($len) {
        $otp = '';
        for($i = 0; $i < $len; $i++) {$otp .= mt_rand(0, 9);}
        return $otp;
    }

    protected function providerUserFromIdToken($idToken){
        
        $client = new Google_Client(['client_id' => config('services.google.client_id')]);
        $providerUser = null;
        $payload = $client->verifyIdToken($idToken);

        if($payload){

            $providerUser = new ProviderUser();
            $providerUser->id = $payload["sub"];
            $providerUser->name = $payload["name"];
            $providerUser->email = $payload["email"];
            $providerUser->avatar = $payload["picture"];
        }

        return $providerUser;
    }

    protected function socialite($provider, $socialToken){

        $providerUser = null;
        if($provider=="google"){
            $providerUser = $this->providerUserFromIdToken($socialToken);
        }else{
            try{
                $providerUser = Socialite::driver($provider)->fields(['email', 'name'])->userFromToken($socialToken);
            }catch(Exception $e){}
        }
        return $providerUser;

    }
}
