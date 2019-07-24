<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

use App\Http\Traits\ResponseUtilities;
use App\Http\Traits\CRUDUtilities;

use App\Models\Voucher;
use App\Models\VoucherInstance;

use App\Http\Resources\Voucher as VoucherResource;
use App\Http\Resources\VoucherInstance as VoucherInstanceResource;

class SharedController extends Controller
{
    use ResponseUtilities, CRUDUtilities;

    private $data;

    public function __construct(){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

    }

    /*******************************************************************************
     *********************************** Vouchers **********************************
     *******************************************************************************/

    public function getVoucher(Request $request, $id){

        $locale = $request->headers->get('locale');
        App::setLocale($locale);
        
        $voucher = Voucher::find($id);
        if(!$voucher || $voucher->deactivated){$this->initResponse(400, 'get_voucher_fail');}
        else{$this->initResponse(200, 'success', __('constants.default_locale')!=App::getLocale()? new VoucherResource($voucher): $voucher);}
        return response()->json($this->data, 200);
    }
    
    public function getVouchers(Request $request){

        $locale = $request->headers->get('locale');
        App::setLocale($locale);
        
        $query1 = Voucher::where('deactivated', false);
        $query2 = clone $query1;
        $query3 = clone $query1;

        $items = __('constants.default_locale')!=App::getLocale()? VoucherResource::collection($query1->paginate(10)): $query1->paginate(10)->items();
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

    public function getVoucherInstance(Request $request, $id){

        $locale = $request->headers->get('locale');
        App::setLocale($locale);

        $user = auth()->user();
        $voucherInstance = VoucherInstance::find($id);
        
        if(!$voucherInstance | $voucherInstance->deactivated){$this->initResponse(400, 'get_voucher_fail');}
        else { $this->initResponse(200, 'success', new VoucherInstanceResource($voucherInstance)); }
        return response()->json($this->data, 200);
    }

    public function getVoucherInstances(Request $request){

        $locale = $request->headers->get('locale');
        App::setLocale($locale);

        $user = auth()->user();
        $voucherInstances = VoucherInstance::where('user_id', $user->id)->where('deactivated', false)->get();
        
        $this->initResponse(200, 'success', VoucherInstanceResource::collection($voucherInstances));
        return response()->json($this->data, 200);
    }

    public function getUser(Request $request, $id){
        
        if (!$user = getDataRowByPrimaryKey(User::class, $id)) { $this->initResponse(500, 'server_error'); }
        $this->initResponse(200, 'success', $user);
        return response()->json($this->data, 200);

    }
}
