<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Traits\ResponseUtilities;

use Carbon\Carbon;

use App\Models\Transaction;
use App\Models\TransactionPoints;
use App\Models\VoucherInstance;

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

        $transactionAttributes = $request->only('user_id', 'invoice_number', 'invoice_value');

        $points = $this->resolvePoints($transactionAttributes['invoice_value']);
        $newTransaction = Transaction::create($transactionAttributes);
        $newTransactionPoints = TransactionPoints::create(['transaction_id' => $newTransaction->id, 'original' => $points]);

        // Carbon::now()->toDateString()
        $voucherInstanceAttributes = ['transaction_id' => $newTransaction->id, 'user_at' => Carbon::now()];
        if($voucherInstanceId = $request->voucher_id){$this->updateVoucherInstance($voucherInstanceId, $voucherInstanceAttributes);}

        $this->initResponse(200, 'add_transactions_success');
        return response()->json($this->data, 200);

    }

    public function checkVoucherInstance(Request $request){

        return response()->json($this->data, 200);

    }

    public function refundTransaction(Request $request){

        $attributes = $request->only('invoice_number');
        if(!$transaction = Transaction::where('invoice_number', $attributes['invoice_number'])->first()){$this->initResponse(400, 'invalid_invoice_number');}
        else{
            if($transaction->transactionPoints->refunded_at){$this->initResponse(400, 'already_refunded');}
            else{
                $transaction->transactionPoints->update(['refunded_at' => Carbon::now()]);
                $this->initResponse(200, 'refund_success');
            }
        }

        return response()->json($this->data, 200);

    }

    /*******************************************************************************
     ********************************* Utilities ***********************************
     *******************************************************************************/

    protected function resolvePoints($invoice_value){

        /**
         * TODO: use the settings to get points corresponds to invoice value
         */
        $points = 500;
        return $points;

    }

    protected function updateVoucherInstance($voucherInstanceId, $attributes){

        $voucherInstance = VoucherInstance::find($voucherInstanceId);
        if(!$voucherInstance){return false;}
        $voucherInstance->update($attributes);
        return true;
        
    }
}
