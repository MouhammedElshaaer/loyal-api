<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

use App\Http\Requests\HomeContentRequest;
use App\Http\Requests\RedeemVoucherRequest;

use Carbon\Carbon;

use App\Models\Voucher;
use App\Models\VoucherInstance;
use App\Models\ActionLog;
use App\User;

use App\Http\Resources\Voucher as VoucherResource;
use App\Http\Resources\VoucherInstance as VoucherInstanceResource;
use App\Http\Resources\Ads as AdsResource;
use App\Http\Resources\TransactionPoints as TransactionPointsResource;

class HomeController extends Controller
{
    
    public function homeContent(HomeContentRequest $request){
        
        $user = auth()->user();
        $ads = $this->getAds();

        $latest_expire_points = $user->latest_expire;
        $trendingRewards = Voucher::where('deactivated', false)->orderBy('instances', 'desc')->take(5)->get();
        $latestVoucherInstances = VoucherInstance::where('user_id', $user->id)
                                                    ->where('deactivated', false)
                                                    ->take(5)
                                                    ->get();
        $latestExpire = null;
        if(count($latest_expire_points) > 0){
            $latestExpire = Carbon::parse($latest_expire_points[0]->valid_end_date)->toFormattedDateString();
        }

        if (config('constants.default_locale')!=$request->headers->get('locale')) {
            $trendingRewards = VoucherResource::collection($trendingRewards);
        }

        $homeContent = [
            'total_points' => $user->total_points,
            'total_expire' => $user->total_expire,
            'latest_expire' => $latestExpire,
            'latest_expire_points' => TransactionPointsResource::collection($latest_expire_points),
            'ads' => AdsResource::collection($ads),
            'trending_rewards' => $trendingRewards,
            'latest_vouchers' => VoucherInstanceResource::collection($latestVoucherInstances)
        ];

        $this->initResponse(200, 'success', $homeContent);
        return response()->json($this->data , 200);

    }

    public function redeemVoucher(RedeemVoucherRequest $request){

        $user = auth()->user();
        $voucher = Voucher::find($request->voucher_id);
        if (!$voucher || $voucher->deactivated) { $this->initResponse(400, 'get_voucher_fail'); }
        else if ($user->total_points < $voucher->points) { $this->initResponse(400, 'no_enough_points'); }
        else {
            $attributes = [
                'user_id'=>$user->id,
                'voucher_id'=>$voucher->id,
                'qr_code'=>$this->idstamping($this->timestamping($this->generateCode(5)), auth()->user()->id, true)
            ];

            /**Starting Transaction */
            \DB::beginTransaction();

            try {

                $voucherInstance = VoucherInstance::create($attributes);
                $voucher->instances += 1;
                $voucher->save();
                
                $status = 'redeem_success';
                $actionScope = 'voucher';
                $actionType = $this->resolveActionFromStatus($actionScope, $status);

                $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $voucherInstance->id, VoucherInstance::class, 'customer', $actionType);
                $this->createUpdateDataRow(ActionLog::class, $actionLogAttributes);

                /**
                 * Note the next commented lines query the database to get the user model 
                 */
                // $totalPoints = $this->getDataRowByPrimaryKey(User::class, $user->id)->total_points;
                // $data = ['total_points' => $totalPoints];

                /**
                 * Note that the query used to calculate the total_points attribute is cached
                 * in the $user model instance, so updates on tables related to this query won't
                 * be reflected in the attribute value. So, I subtract the voucher points from the
                 * total_points attribute to make use of the cached query result in $user model
                 */
                $data = ['total_points' => $user->total_points - $voucher->points];

                /**Commiting Transaction */
                \DB::commit();

                $this->initResponse(200, $status, $data);

            } catch (Exception $e) {
                /**Rollback Transaction */
                \DB::rollBack();
                $this->initResponse(400, 'custom_message', null, ['message'=>$e->getMessage()]);
            }
        }
        return response()->json($this->data , 200);
    }

    /*******************************************************************************
     ********************************* Utilities ***********************************
     *******************************************************************************/
    
}
