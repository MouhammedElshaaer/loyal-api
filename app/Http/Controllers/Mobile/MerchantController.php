<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Resources\VoucherInstance as VoucherInstanceResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\ActionLog as ActionLogResource;

use App\Http\Requests\AddTransactionRequest;
use App\Http\Requests\CheckVoucherInstanceRequest;
use App\Http\Requests\RefundTransactionRequest;
use App\Http\Requests\CustomerFromQRCodeRequest;

use App\Jobs\SendNotification;

use Exception;
use Carbon\Carbon;

use App\User;

use App\Models\Transaction;
use App\Models\TransactionPoints;
use App\Models\VoucherInstance;
use App\Models\ActionLog;
use App\Models\Device;

class MerchantController extends Controller
{

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
            $user = $this->getDataRow(User::class, 'qr_code', $request->user_qr_code);
            if (!$user) { throw new Exception('User not found'); }
            else if (!$user->verified) { throw new Exception('User not verified'); }

            $transactionAttributes = $request->only('invoice_number', 'invoice_value');
            $transactionAttributes['user_id'] = $user->id;
            $transaction = $this->createUpdateDataRow(Transaction::class, $transactionAttributes);

            $transactionPointsAttributes = ['transaction_id' => $transaction->id, 'original' => $points];
            $transactionPoints = $this->createUpdateDataRow(TransactionPoints::class, $transactionPointsAttributes);

            $status = 'add_transaction_success';
            $actionScope = 'transaction';
            $actionType = $this->resolveActionFromStatus($actionScope, $status);

            $this->initResponse(200, $status);

            if ($request->has('voucher_qr_code')) {

                $voucherInstance = $this->getDataRow(VoucherInstance::class, 'qr_code', $request->voucher_qr_code);
                if (!$voucherInstance){ throw new Exception("Voucher not found"); }
                else if ($voucherInstance->is_used){ throw new Exception("Voucher already used"); }
                else if ($voucherInstance->deactivated){ throw new Exception("Voucher deactivated"); }
                else if ($voucherInstance->user_id != $user->id) { throw new Exception("User unauthorized to use this voucher"); }
                else {

                    $voucherInstanceAttributes = [
                        'id' => $voucherInstance->id,
                        'transaction_id' => $transaction->id,
                        'used_at' => Carbon::now()
                    ];

                    if ($this->createUpdateDataRow(VoucherInstance::class, $voucherInstanceAttributes)) {

                        $status = 'voucher_used_success';
                        $actionType = $this->resolveActionFromStatus($actionScope, $status);

                        $this->initResponse(200, $status);

                    } else { throw new Exception("Failed to use this voucher"); }
                }
            }


            /**
                Notifications to be test
             */
            // $tokens = $this->getDataRows(Device::class, 'user_id', $user->id)
            //             ->map(function ($device) { return $device->token; })
            //             ->toArray();

            // if (count($tokens)) {

            //     dispatch(new SendNotification(
            //         $this->notificationsService,
            //         $tokens, //device tokens that will be notified
            //         ucfirst(str_replace("_", " ", $status)), //notification title
            //         __('messages.'.$status) //notification body
            //     ));

            // }

            $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $transaction->id, Transaction::class, 'cashier', $actionType);
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

                $data = [
                    'voucher' => new VoucherInstanceResource($voucherInstance),
                    'user' => new UserResource($voucherInstance->user)
                ];
                $this->initResponse(200, $statusCode, $data);
                $actionType = $this->resolveActionFromStatus($actionScope, $status);
                $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $voucherInstance->id, VoucherInstance::class, 'cashier', $actionType);

            }
            else {
                $this->initResponse(400, $statusCode);
                $actionType = $this->resolveActionFromStatus($actionScope, $status);
                $actionLogAttributes = $this->initLogAttributes(auth()->user()->id, $voucherInstance->id, VoucherInstance::class, 'cashier', $actionType);

            }

            /**
                Notifications to be test
             */
            // $tokens = $this->getDataRows(Device::class, 'user_id', $voucherInstance->user_id)
            //             ->map(function ($device) { return $device->token; })
            //             ->toArray();

            // if (count($tokens)) {

            //     dispatch(new SendNotification(
            //         $this->notificationsService,
            //         $tokens, //device tokens that will be notified
            //         ucfirst(strtolower(str_replace("_", " ", $status))) //notification title
            //     ));

            // }

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

                /**
                    Notifications to be test
                */
                // $tokens = $this->getDataRows(Device::class, 'user_id', $transaction->user_id)
                //             ->map(function ($device) { return $device->token; })
                //             ->toArray();

                // if (count($tokens)) {

                //     dispatch(new SendNotification(
                //         $this->notificationsService,
                //         $tokens, //device tokens that will be notified
                //         ucfirst(str_replace("_", " ", $status)), //notification title
                //         __('messages.'.$status) //notification body
                //     ));

                // }

            } else { $this->initResponse(400, 'already_refunded'); }

        } else{ $this->initResponse(400, 'invalid_invoice_number'); }

        return response()->json($this->data, 200);

    }

    /*******************************************************************************
     ******************** Terms & Conditions, Policies and About *******************
     *******************************************************************************/

    public function dashboard(Request $request){

        $dashboardContent = [
            'terms_conditions' => $this->getSetting(config('constants.settings.terms_conditions'))->value,
            'policies' => $this->getSetting(config('constants.settings.policies'))->value,
            'about' => $this->getSetting(config('constants.settings.about'))->value
        ];

        $this->initResponse(200, 'success', $dashboardContent);
        return response()->json($this->data , 200);

    }

    /*******************************************************************************
     ***************************** Customer Validation *****************************
     *******************************************************************************/

    public function getCustomerFromQRCode(CustomerFromQRCodeRequest $request){

        $customer = $this->getDataRow(User::class, 'qr_code', $request->qr_code);
        if (!$customer) { $this->initResponse(400, 'user_validation_fail'); }
        else if ($customer->deactivated) { $this->initResponse(400, 'deactivated_account'); }
        else if (!$customer->verified) { $this->initResponse(400, 'non_verified'); }
        else { $this->initResponse(200, 'user_validation_success', new UserResource($customer)); }
        return response()->json($this->data , 200);

    }

    /*******************************************************************************
     ********************************* Action Logs *********************************
     *******************************************************************************/

    public function getActionLogs(Request $request){

        $actionLogs = $this->getDataRows(ActionLog::class, 'user_id', auth()->user()->id);

        $this->initResponse(200, 'success', ActionLogResource::collection($actionLogs));
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
