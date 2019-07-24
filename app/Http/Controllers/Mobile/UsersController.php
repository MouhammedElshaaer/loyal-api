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
use App\Http\Requests\AddReportRequest;

use Exception;
use Google_Client;

use App\Http\Traits\ResponseUtilities;
use App\Http\Traits\CRUDUtilities;
use App\Http\Traits\CodeGenerationUtilities;

use App\User;
use App\Models\LinkedSocialAccount;
use App\Models\Report;
use App\Models\Role;

class UsersController extends Controller
{
    use ResponseUtilities, CRUDUtilities, CodeGenerationUtilities;

    private $data;

    public function __construct(){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

    }


    /*******************************************************************************
     ****************************** Authentication *********************************
     *******************************************************************************/

    public function login(LoginRequest $request){

        $attributes = $request->only('country_code', 'phone', 'password');

        if(!auth()->attempt($attributes)){
            $this->initResponse(401, 'login_fail');
        }else{
            $user = auth()->user();
            $this->verifiedResponse($user);
        }
        return response()->json($this->data, 200);
    }

    public function socailLogin(SocialLoginRequest $request)
    {
        $provider =  $request->provider_name;
        $socialToken = $request->social_token;
        $providerUser = null;
        
        $providerUser = $this->socialite($provider, $socialToken);
        if ($providerUser) {

            if($providerUser->getEmail()){$user = $this->findOrCreate($providerUser, $provider);}
            else{$this->initResponse(400, 'missing_associated_email');}

        }else{$this->initResponse(400, 'social_signup_fail');}

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

                if (!$role = $this->getDataRowByKey(Role::class, 'name', 'customer')) { $user->roles()->attach($role); }
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

        $user = $this->createUpdateDataRow(User::class, $attributes);
        if($user){

            if ($role = $this->getDataRowByKey(Role::class, 'name', 'customer')) { $user->roles()->attach($role); }
            $this->signupSuccessResponse($user);
        }

        return response()->json($this->data , 200);
    }

    public function completeSignup(CompleteSignupRequest $request){
        
        $socialToken = $request->social_token;
        $provider =  $request->provider_name;
        $attributes = $request->only('country_code', 'phone', 'name');
        $user = null;

        $providerUser = $this->socialite($provider, $socialToken);
        if ($providerUser) {

            if($providerUser->getEmail()){$user = $this->findOrCreate($providerUser, $provider);}
            else{$this->initResponse(400, 'missing_associated_email');}
            
            if(!$user){$this->initResponse(400, 'user_validation_fail');}
            else{
                $user->update($attributes);
                $this->signupSuccessResponse($user);
            }

        }else{$this->initResponse(400, 'social_signup_fail');}

        return response()->json($this->data , 200);
    }

    public function verifyAccount(UserVerificationRequest $request){

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

        return response()->json($this->data , 200);
    }

    public function validateUser(ValidateUserRequest $request){

        $attributes = $request->only('country_code', 'phone');
        $user = User::where("phone", $request->phone)->where("country_code", $request->country_code)->first();
        if($user){
            $this->initResponse(200, 'user_validation_success');
            $this->sendVerificationCode($user->id);
        }else{$this->initResponse(400, 'user_validation_fail');}

        return response()->json($this->data , 200);
    }

    public function verifyPhone(VerifyPhoneRequest $request){

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
 
        return response()->json($this->data , 200);
    }

    public function resetPassword(ResetPasswordRequest $request){

        $attributes = $request->only('password', 'code');
        
        if(auth()->user()){
            $user = User::where('id', auth()->user()->id)->first();
            if($user){
                $user->password = bcrypt($attributes['password']);
                $user->otp = null;
                $user->save();

                auth()->setUser($user);
                $this->initResponse(200, 'password_reset_success');

            }else{$this->initResponse(400, 'user_validation_fail');}
        }else{$this->initResponse(400, 'unauthorized');}
        return response()->json($this->data , 200);
    }

    public function resendCode(ResendCodeRequest $request){

        $user = User::where("phone", $request->phone)->where("country_code", $request->country_code)->first();
        if(!$user){$this->initResponse(400, 'resend_code_fail');}
        else{
            //Update the user instance in the DB
            $user->otp = null;
            $user->save();

            $this->sendVerificationCode($user->id);
            $this->initResponse(200, 'resend_code_success');
        }

        return response()->json($this->data , 200);
    }

    public function logout(Request $request){
        
        if(auth()->user()){

            $accessToken = auth()->user()->token();
            \DB::table('oauth_refresh_tokens')->where('access_token_id', $accessToken->id)->update(['revoked' => true]);
            $accessToken->revoke();
            $this->initResponse(200, 'logout_success');

        }else{$this->initResponse(400, 'unauthorized');}
        return response()->json($this->data , 200);
    }


    
    /*******************************************************************************
     *********************************** Reports ***********************************
     *******************************************************************************/

    public function addReport(AddReportRequest $request){

        $attributes = $request->only('user_id', 'message', 'attachment');
        if( User::find($attributes['user_id']) ){
            $report = Report::create($attributes);
            $this->initResponse(200, 'add_report_success');
        }else{throw new Exception();}

        return response()->json($this->data, 200);
    }

    /*******************************************************************************
     ********************************* Utilities ***********************************
     *******************************************************************************/

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
