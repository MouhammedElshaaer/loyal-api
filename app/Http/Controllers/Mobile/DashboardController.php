<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\AddReportRequest;
use App\Http\Requests\AddUpdateVoucherRequest;

use App\Http\Traits\ResponseUtilities;

use Exception;

use App\Models\Report;
use App\Models\Voucher;
use App\User;

class DashboardController extends Controller
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
    
    /*******************************************************************************
     *********************************** Reports ***********************************
     *******************************************************************************/

    public function getReport(){
        
        if(!$report = Report::find($id)){$this->initResponse(400, 'get_report_fail');}
        else{$this->initResponse(200, 'get_report_success', $report);}
        return response()->json($this->data, 200);
    }
    
    public function getReports(){

        $reports = Report::all();
        $this->initResponse(200, 'get_reports_success', $reports);
        return response()->json($this->data, 200);
    }

    public function addReport(AddReportRequest $request){

        $attributes = $request->only('user_id', 'message', 'attachment');
        if( User::find($attributes['user_id']) ){
            $report = Report::create($attributes);
            $this->initResponse(200, 'add_report_success');
        }else{throw new Exception();}

        return response()->json($this->data, 200);
    }

    public function updateReport(UpdateReportRequest $request, $id){
        $attributes = $request->only('message', 'attachment');
        if(!$report = Report::find($id)){$this->initResponse(400, 'update_report_fail');}
        else{
            $report->update($attributes);
            $this->initResponse(200, 'update_report_success');
        }
        return response()->json($this->data, 200);
    }

    public function deleteReport($id){
        
        $report = Report::find($id);
        if(!$report){$this->initResponse(400, 'get_report_fail');}
        else{
            $report->delete();
            $this->initResponse(200, 'delete_report_success');
        }
        return response()->json($this->data, 200);
    }
    
    /*******************************************************************************
     *********************************** Vouchers **********************************
     *******************************************************************************/

    public function getVoucher($id){
        if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'get_voucher_fail');}
        else{$this->initResponse(200, 'get_voucher_success', $voucher);}
        return response()->json($this->data, 200);
    }
    
    public function getVouchers(Request $request){

        $query1 = Voucher::where('deactivated', false);
        $query2 = clone $query1;
        $query3 = clone $query1;

        $items = $query1->paginate(10)->items();
        $total_pages = $query2->paginate(10)->lastPage();
        $total_items = $query3->paginate(10)->total();
        $paginate_response = [
            'vouchers' => $items? $items: [],
            'total_pages' => $total_pages,
            'total_items' => $total_items
        ];
        
        $this->initResponse(200, 'get_vouchers_success', $paginate_response);
        return response()->json($this->data, 200);
    }

    public function addVoucher(AddUpdateVoucherRequest $request){

        $attributes = $request->only('points', 'title', 'description', 'image');
        $voucher = Voucher::create($attributes);
        $this->initResponse(200, 'add_voucher_success');
        return response()->json($this->data, 200);
    }

    public function updateVoucher(AddUpdateVoucherRequest $request, $id){

        $attributes = $request->only('value', 'points', 'title', 'description', 'deactivated');
        if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'update_voucher_fail');}
        else{
            $voucher->update($attributes);
            $this->initResponse(200, 'update_voucher_success');
        }
        return response()->json($this->data, 200);
    }

    public function deleteVoucher($id){
       
        if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'get_voucher_fail');}
        else{
            $voucher->delete();
            $this->initResponse(200, 'delete_voucher_success');
        }
        return response()->json($this->data, 200);
    }

    public function homeContent(Request $request){
        
        if(auth()->guard('api')->check()){

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

            $this->data['data'] = $homeContent;

        }else{$this->initResponse(400, 'unauthorized');}

        return response()->json($this->data , 200);

    }
}
