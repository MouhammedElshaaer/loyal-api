<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Traits\ResponseUtilities;

class MerchantController extends Controller
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
     ********************************* Transactions ********************************
     *******************************************************************************/

    public function addTransaction(Request $request){

        return response()->json($this->data, 200);

    }

    public function checkVoucherInstance(Request $request){

        return response()->json($this->data, 200);

    }

    public function refundTransaction(Request $request){

        return response()->json($this->data, 200);

    }
}
