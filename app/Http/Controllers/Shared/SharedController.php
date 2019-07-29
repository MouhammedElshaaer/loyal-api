<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

use App\Http\Requests\GetVoucherRequest;
use App\Http\Requests\GetVouchersRequest;
use App\Http\Requests\GetVoucherInstanceRequest;
use App\Http\Requests\GetVoucherInstancesRequest;
use App\Http\Requests\BindDeviceRequest;

use App\Models\Voucher;
use App\Models\VoucherInstance;
use App\Models\Device;

use App\Http\Resources\Voucher as VoucherResource;
use App\Http\Resources\VoucherInstance as VoucherInstanceResource;

class SharedController extends Controller
{

    /*******************************************************************************
     *********************************** Vouchers **********************************
     *******************************************************************************/

    public function getVoucher(GetVoucherRequest $request, $id){
        
        $voucher = Voucher::find($id);
        if(!$voucher || $voucher->deactivated){$this->initResponse(400, 'get_voucher_fail');}
        else{$this->initResponse(200, 'success', config('constants.default_locale')!=App::getLocale()? new VoucherResource($voucher): $voucher);}
        return response()->json($this->data, 200);
    }
    
    public function getVouchers(GetVouchersRequest $request){
        
        $query1 = Voucher::where('deactivated', false);
        $query2 = clone $query1;
        $query3 = clone $query1;

        $items = config('constants.default_locale')!=App::getLocale()? VoucherResource::collection($query1->paginate(10)): $query1->paginate(10)->items();
        $total_pages = $query2->paginate(10)->lastPage();
        $total_items = $query3->paginate(10)->total();
        $paginate_response = [
            'vouchers' => $items? $items: [],
            'total_pages' => $total_pages,
            'total_items' => $total_items
        ];
        
        $this->initResponse(200, 'success', $paginate_response);
        return response()->json($this->data, 200);
    }

    /*******************************************************************************
     ****************************** VoucherIntances *******************************
     *******************************************************************************/

    public function getVoucherInstance(GetVoucherInstanceRequest $request, $id){

        $user = auth()->user();
        $voucherInstance = VoucherInstance::find($id);
        
        if(!$voucherInstance | $voucherInstance->deactivated){$this->initResponse(400, 'get_voucher_fail');}
        else { $this->initResponse(200, 'success', new VoucherInstanceResource($voucherInstance)); }
        return response()->json($this->data, 200);
    }

    public function getVoucherInstances(GetVoucherInstancesRequest $request){

        $user = auth()->user();
        $voucherInstances = VoucherInstance::where('user_id', $user->id)->where('deactivated', false)->get();
        
        $this->initResponse(200, 'success', VoucherInstanceResource::collection($voucherInstances));
        return response()->json($this->data, 200);
    }

    /*******************************************************************************
     ************************************ Users ************************************
     *******************************************************************************/

    public function getUser(Request $request, $id){
        
        if (!$user = getDataRowByPrimaryKey(User::class, $id)) { $this->initResponse(500, 'server_error'); }
        $this->initResponse(200, 'success', $user);
        return response()->json($this->data, 200);

    }

    /*******************************************************************************
     ******************************** Notifications ********************************
     *******************************************************************************/

    public function bindDevice(BindDeviceRequest $request){
        
        $user = auth()->user();
        $attributes = $request->only('token', 'type');
        $attributes['user_id'] = $user->id;
        $device = $this->createUpdateDataRow(Device::class, $attributes);

        $this->initResponse(200, 'success');
        if (!$device) { initResponse(500, 'server_error'); }

        return response()->json($this->data, 200);

    }
}
