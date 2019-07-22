<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\AddTransactionRequest;
use App\Http\Requests\CheckVoucherInstanceRequest;
use App\Http\Requests\RefundTransactionRequest;

use App\Http\Traits\ResponseUtilities;
use App\Http\Traits\SettingUtilities;
use App\Http\Traits\CRUDUtilities;

use Exception;
use Carbon\Carbon;

use App\Models\Transaction;
use App\Models\TransactionPoints;
use App\Models\VoucherInstance;

class MerchantController extends Controller
{
    use ResponseUtilities, CRUDUtilities, SettingUtilities;

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

    public function addTransaction(AddTransactionRequest $request){

        /**Starting Transaction */
        \DB::beginTransaction();

        /**
         * TODO: Check the invoice_value if greater than or equal to a premium category add this role to user 
         */

        try {

            $points = $this->resolvePoints($request->invoice_value);
            $transactionAttributes = $request->only('user_id', 'invoice_number', 'invoice_value');
            $newTransaction = $this->createUpdateDataRow(Transaction::class, $transactionAttributes);

            $transactionPointsAttributes = ['transaction_id' => $newTransaction->id, 'original' => $points];
            $newTransactionPoints = $this->createUpdateDataRow(TransactionPoints::class, $transactionPointsAttributes);
            $this->initResponse(200, 'add_transactions_success');

            if ($request->has('voucher_id')) {
                $voucherInstance = VoucherInstance::find($request->voucher_id);
                if (!$voucherInstance){ throw new Exception("Voucher not found"); }
                else if ($voucherInstance->deactivated){ throw new Exception("Voucher deactivated"); }
                if ($voucherInstance) {
    
                    $voucherInstanceAttributes = [
                        'id' => $voucherInstance->id,
                        'transaction_id' => $newTransaction->id,
                        'used_at' => Carbon::now()
                    ];
                    if (!$this->createUpdateDataRow(VoucherInstance::class, $voucherInstanceAttributes)) {
                        throw new Exception("Failed to use this voucher");    
                    } else { $this->initResponse(200, 'voucher_used_success'); }
                }
            }

            /**Commiting Transaction */
            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            $this->initResponse(400, 'custom_message', null, ['message'=>$e->getMessage()]);
        }


        return response()->json($this->data, 200);

    }

    public function checkVoucherInstance(CheckVoucherInstanceRequest $request){

        if ($voucherInstance = VoucherInstance::find($request->voucher_id)) {

            if ($voucherInstance->is_valid) { $this->initResponse(200, 'valid_voucher'); }
            else { $this->initResponse(400, strval($voucherInstance->status)); }
            
        }
        else { $this->initResponse(400, 'get_voucher_fail'); }

        return response()->json($this->data, 200);

    }

    public function refundTransaction(RefundTransactionRequest $request){

        if ($transaction = Transaction::where('invoice_number', $request->invoice_number)->first()) {

            if(!$transaction->transactionPoints->refunded_at){

                $transaction->transactionPoints->update(['refunded_at' => Carbon::now()]);
                $this->initResponse(200, 'refund_success');

            } else { $this->initResponse(400, 'already_refunded'); }

        } else{ $this->initResponse(400, 'invalid_invoice_number'); }

        return response()->json($this->data, 200);

    }

    /*******************************************************************************
     ********************************* Utilities ***********************************
     *******************************************************************************/

    protected function resolvePoints($invoice_value){

        $points_per_currency_unit = $this->getSetting(__('constants.points_per_currency_unit'))->value;
        $currency_unit = $this->getSetting(__('constants.currency_unit'))->value;

        if ($currency_unit > 0 && $points_per_currency_unit > 0){
            return (int) (($invoice_value/$currency_unit) * $points_per_currency_unit);
        } else{return 0;}

    }
}
