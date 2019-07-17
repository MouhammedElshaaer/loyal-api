<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

use App\Http\Traits\ResponseUtilities;

use App\Models\Voucher;

use App\Http\Resources\Voucher as VoucherResource;

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
     *********************************** Vouchers **********************************
     *******************************************************************************/

    public function getVoucher(Request $request, $id){

        $locale = $request->headers->get('locale');
        App::setLocale($locale);
        
        if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'get_voucher_fail');}
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
}
