<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Traits\ResponseUtilities;

use App\Models\Voucher;

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
    
        $user = auth()->guard('api')->user();
        $ads = [
            0 => 'http://localhost:8000/api/public/ad1.jpg',
            1 => 'http://localhost:8000/api/public/ad1.jpg',
            2 => 'http://localhost:8000/api/public/ad1.jpg',
            3 => 'http://localhost:8000/api/public/ad1.jpg'
        ];
        $latestRewards = Voucher::where('deactivated', false)->orderBy('instances', 'desc')->take(5)->get();
        
        $latestVouchers = [
            0 => ['id'=>1, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"0", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>null],
            1 => ['id'=>2, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"1", 'created_at'=>"Jul 14, 2019", 'expired_at'=>"Jul 14, 2019", 'used_at'=>null],
            2 => ['id'=>3, 'transaction_id'=>2, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"2", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>"Jul 14, 2019"],
            3 => ['id'=>4, 'transaction_id'=>3, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"2", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>"Jul 14, 2019"],
            4 => ['id'=>5, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"0", 'created_at'=>"Jul 14, 2019", 'expired_at'=>'Jul 14, 2019', 'used_at'=>null]
        ];

        $homeContent = [
            'total_points' => $user->total_points,
            'total_expire' => $user->total_expire,
            'latest_expire' => $user->latest_expire,
            'ads' => $ads,
            'latest_rewards' => $latestRewards,
            'latest_vouchers' => $latestVouchers
        ];

        $this->initResponse(200, 'success', $homeContent);
        // $this->data['data'] = ;

        return response()->json($this->data , 200);

    }
}
