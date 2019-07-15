<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\AddReportRequest;

use App\Http\Traits\ResponseUtilities;

class SharedController extends Controller
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

    public function addReport(AddReportRequest $request){

        $attributes = $request->only('user_id', 'message', 'attachment');
        if( User::find($attributes['user_id']) ){
            $report = Report::create($attributes);
            $this->initResponse(200, 'add_report_success');
        }else{throw new Exception();}

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
}
