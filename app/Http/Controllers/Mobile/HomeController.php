<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

use App\Http\Traits\ResponseUtilities;

use App\Models\Voucher;

use App\Http\Resources\Voucher as VoucherResource;

class HomeController extends Controller
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
    
    public function homeContent(Request $request){
    
        $locale = $request->headers->get('locale');
        App::setLocale($locale);

        $user = auth()->guard('api')->user();
        $ads = [
            0 => 'https://designshack.net/wp-content/uploads/background-design-trends.jpg',
            1 => 'https://designshack.net/wp-content/uploads/background-design-trends.jpg',
            2 => 'https://designshack.net/wp-content/uploads/background-design-trends.jpg',
            3 => 'https://designshack.net/wp-content/uploads/background-design-trends.jpg'
        ];
        $trendingRewards = Voucher::where('deactivated', false)->orderBy('instances', 'desc')->take(5)->get();
        
        $latestVouchers = [
            0 => ['id'=>1, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"0", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>null],
            1 => ['id'=>2, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"1", 'created_at'=>"Jul 14, 2019", 'expired_at'=>"Jul 14, 2019", 'used_at'=>null],
            2 => ['id'=>3, 'transaction_id'=>2, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"2", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>"Jul 14, 2019"],
            3 => ['id'=>4, 'transaction_id'=>3, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"2", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>"Jul 14, 2019"],
            4 => ['id'=>5, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"0", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>null]
        ];

        if(__('constants.default_locale')!=$request->headers->get('locale')){
            $trendingRewards = VoucherResource::collection($trendingRewards);
        }

        $homeContent = [
            'total_points' => $user->total_points,
            'total_expire' => $user->total_expire,
            'latest_expire' => $user->latest_expire,
            'ads' => $ads,
            'trending_rewards' => $trendingRewards,
            'latest_vouchers' => $latestVouchers
        ];

        $this->initResponse(200, 'success', $homeContent);

        return response()->json($this->data , 200);

    }
}
