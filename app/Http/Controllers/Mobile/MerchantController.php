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
use App\Http\Traits\LogUtilities;
use App\Http\Traits\StatusUtilities;

use Exception;
use Carbon\Carbon;

use App\User;

use App\Models\Transaction;
use App\Models\TransactionPoints;
use App\Models\VoucherInstance;
use App\Models\ActionLog;

class MerchantController extends Controller
{
    use ResponseUtilities, CRUDUtilities, SettingUtilities, LogUtilities, StatusUtilities;

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
            $user = $this->getDataRowByKey(User::class, 'phone', $request->user_phone);

            $transactionAttributes = $request->only('invoice_number', 'invoice_value');
            $transactionAttributes['user_id'] = $user->id;
            $transaction = $this->createUpdateDataRow(Transaction::class, $transactionAttributes);

            $transactionPointsAttributes = ['transaction_id' => $transaction->id, 'original' => $points];
            $transactionPoints = $this->createUpdateDataRow(TransactionPoints::class, $transactionPointsAttributes);

            $status = 'add_transaction_success';
            $actionScope = 'transaction';
            $actionType = $this->resolveActionFromStatus($actionScope, $status);

            $this->initResponse(200, $status);
            $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $transaction->id, Transaction::class, 'cashier', $actionType);

            if ($request->has('voucher_id')) {

                $voucherInstance = VoucherInstance::find($request->voucher_id);
                if (!$voucherInstance){ throw new Exception("Voucher not found"); }
                else if ($voucherInstance->deactivated){ throw new Exception("Voucher deactivated"); }
                else {
    
                    $voucherInstanceAttributes = [
                        'id' => $voucherInstance->id,
                        'transaction_id' => $transaction->id,
                        'used_at' => Carbon::now()
                    ];

                    if ($this->createUpdateDataRow(VoucherInstance::class, $voucherInstanceAttributes)) {
                      
                        $status = 'voucher_used_success';
                        $actionType = $this->resolveActionFromStatus($actionScope, $status);
                        $newAttributes = ['action_id' => $this->getAction($actionType)->id];
                        
                        $this->initResponse(200, $status);
                        $actionLogAttributes = $this->updateLogAttributes($actionLogAttributes, $newAttributes);  

                    } else { throw new Exception("Failed to use this voucher"); }
                }
            }

            $this->createUpdateDataRow(ActionLog::class, $actionLogAttributes);

            /**Commiting Transaction */
            \DB::commit();

        } catch (Exception $e) {
            /**Rollback Transaction */
            \DB::rollBack();
            $this->initResponse(400, 'custom_message', null, ['message'=>$e->getMessage()]);
        }


        return response()->json($this->data, 200);

    }

    public function checkVoucherInstance(CheckVoucherInstanceRequest $request){

        $actionLogAttributes = null;
        if ($voucherInstance = VoucherInstance::where('qr_code', $request->qr_code)->first()) {

            $statusCode = strval($voucherInstance->status);
            $status = $this->resolveStatusFromStatusCode($statusCode);
            $actionScope = 'voucher_instance_check';

            if ($voucherInstance->is_valid) {

                $data = ['voucher_id'=>$voucherInstance->id];
                $this->initResponse(200, $statusCode, $data);
                $actionType = $this->resolveActionFromStatus($actionScope, $status);
                $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $voucherInstance->id, VoucherInstance::class, 'cashier', $actionType);

            }
            else {
                $this->initResponse(400, $statusCode);
                $actionType = $this->resolveActionFromStatus($actionScope, $status);                
                $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $voucherInstance->id, VoucherInstance::class, 'cashier', $actionType);

            }
            
        }
        else { $this->initResponse(400, 'get_voucher_fail'); }

        if ($actionLogAttributes) { $this->createUpdateDataRow(ActionLog::class, $actionLogAttributes); }

        return response()->json($this->data, 200);

    }

    public function refundTransaction(RefundTransactionRequest $request){

        if ($transaction = Transaction::where('invoice_number', $request->invoice_number)->first()) {

            if(!$transaction->transactionPoints->refunded_at){

                $status = 'refund_success';
                $actionScope = 'transaction';
                $actionType = $this->resolveActionFromStatus($actionScope, $status);

                $transaction->transactionPoints->update(['refunded_at' => Carbon::now()]);
                $this->initResponse(200, $status);

                $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $transaction->id, Transaction::class, 'cashier', $actionType);
                $this->createUpdateDataRow(ActionLog::class, $actionLogAttributes);

            } else { $this->initResponse(400, 'already_refunded'); }

        } else{ $this->initResponse(400, 'invalid_invoice_number'); }

        return response()->json($this->data, 200);

    }

    /*******************************************************************************
     ********************************* Utilities ***********************************
     *******************************************************************************/

    protected function resolvePoints($invoice_value){

        $points_per_currency_unit = $this->getSetting(config('constants.settings.points_per_currency_unit'))->value;
        $currency_unit = $this->getSetting(config('constants.settings.currency_unit'))->value;

        if ($currency_unit > 0 && $points_per_currency_unit > 0){
            return (int) (($invoice_value/$currency_unit) * $points_per_currency_unit);
        } else{return 0;}

    }
}
