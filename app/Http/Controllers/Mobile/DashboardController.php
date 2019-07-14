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

        $report = null;
        try{

            if(!$report = Report::find($id)){$this->initResponse(400, 'get_report_fail');}
            else{$this->initResponse(200, 'get_report_success', $report);}

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }
    
    public function getReports(){

        $reports = null;
        try{

            $reports = Report::all();
            $this->initResponse(200, 'get_reports_success', $reports);

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function addReport(AddReportRequest $request){

        $attributes = $request->only('user_id', 'message', 'attachment');
        try{

            if( User::find($attributes['user_id']) ){
                $report = Report::create($attributes);
                $this->initResponse(200, 'add_report_success');
            }else{throw new Exception();}

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function updateReport(UpdateReportRequest $request, $id){

        try{

            $attributes = $request->only('message', 'attachment');
            if(!$report = Report::find($id)){$this->initResponse(400, 'update_report_fail');}
            else{
                $report->update($attributes);
                $this->initResponse(200, 'update_report_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function deleteReport($id){
        
        $report = null;
        try{

            $report = Report::find($id);
            if(!$report){$this->initResponse(400, 'get_report_fail');}
            else{
                $report->delete();
                $this->initResponse(200, 'delete_report_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }
    
    /*******************************************************************************
     *********************************** Vouchers **********************************
     *******************************************************************************/

    public function getVoucher(){

        $voucher = null;
        try{

            if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'get_voucher_fail');}
            else{$this->initResponse(200, 'get_voucher_success', $voucher);}

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }
    
    public function getVouchers(){

        $vouchers = null;
        try{

            $vouchers = Voucher::all();
            $this->initResponse(200, 'get_vouchers_success', $vouchers);

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function addVoucher(AddUpdateVoucherRequest $request){

        $attributes = $request->only('value', 'points', 'title', 'description');
        try{

            $voucher = Voucher::create($attributes);
            $this->initResponse(200, 'add_voucher_success');

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function updateVoucher(AddUpdateVoucherRequest $request, $id){

        try{

            $attributes = $request->only('value', 'points', 'title', 'description');
            if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'update_voucher_fail');}
            else{
                $voucher->update($attributes);
                $this->initResponse(200, 'update_voucher_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function deleteVoucher($id){
        
        $voucher = null;
        try{

            if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'get_voucher_fail');}
            else{
                $voucher->delete();
                $this->initResponse(200, 'delete_voucher_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
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
            $latestRewards = [
                0 => ['id'=>1, 'points'=>1300, 'title'=>"50% Discount", 'description'=>"description", 'image'=>'http://localhost:8000/api/public/ad1.jpg'],
                1 => ['id'=>2, 'points'=>1300, 'title'=>"50% Discount", 'description'=>"description", 'image'=>'http://localhost:8000/api/public/ad1.jpg'],
                2 => ['id'=>3, 'points'=>1300, 'title'=>"50% Discount", 'description'=>"description", 'image'=>'http://localhost:8000/api/public/ad1.jpg'],
                3 => ['id'=>4, 'points'=>1300, 'title'=>"50% Discount", 'description'=>"description", 'image'=>'http://localhost:8000/api/public/ad1.jpg'],
                4 => ['id'=>5, 'points'=>1300, 'title'=>"50% Discount", 'description'=>"description", 'image'=>'http://localhost:8000/api/public/ad1.jpg']
            ];
            $latestVouchers = [
                0 => ['id'=>1, 'transaction_id'=>4, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"1", 'created_at'=>"Jul 14, 2019", 'expired_at'=>null, 'used_at'=>"Jul 14, 2019"],
                1 => ['id'=>2, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"0", 'created_at'=>"Jul 14, 2019", 'expired_at'=>"Jul 14, 2019", 'used_at'=>null],
                2 => ['id'=>3, 'transaction_id'=>2, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"1", 'created_at'=>"Jul 14, 2019", 'expired_at'=>null, 'used_at'=>"Jul 14, 2019"],
                3 => ['id'=>4, 'transaction_id'=>3, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"1", 'created_at'=>"Jul 14, 2019", 'expired_at'=>null, 'used_at'=>"Jul 14, 2019"],
                4 => ['id'=>5, 'transaction_id'=>null, 'qr_code'=>"858165981658713", 'title'=>"50% Discount", 'status'=>"0", 'created_at'=>"Jul 14, 2019", 'expired_at'=>null, 'used_at'=>null]
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
