<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\FetchSettingsRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\AddUpdateVoucherRequest;

use App\Http\Traits\ResponseUtilities;
use App\Http\Traits\LocaleUtilities;

use App\Models\Report;
use App\Models\Voucher;
use App\Models\Configuration;
use App\Models\Setting;
use App\Models\Translation;

use Exception;

class AdminController extends Controller
{
    use ResponseUtilities, LocaleUtilities;

    private $data;

    public function __construct(){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

    }
    
    /*******************************************************************************
     ********************************** Settings ***********************************
     *******************************************************************************/
    public function fetchSettings(FetchSettingsRequest $request){

        foreach($request->data["settings"] as $settingName=>$settingValue){
            $configuration = Configuration::where('category', $settingName)->first();
            if(!$setting = Setting::where('configuration_id', $configuration->id)->first()){
                $setting = Setting::create(['configuration_id'=>$configuration->id]);
            }
            $setting->value = $settingValue;
            $setting->save();
        }

        $failed = false;
        $adsConfig = Configuration::where('category', 'ADS')->first();
        foreach ($request->data["ads"] as $ad)
        {
            if($ad["new_ad"]){
                $this->insertAd($ad, $adsConfig->id);
            }
            else if ($ad["deleted"]){
                if($failed = !$this->deleteAd($ad["id"])){
                    $this->initResponse(200, 'ad_failed');
                }
            }
            else if ($ad["updated"]){
                if($failed = !$this->updateAd($ad)){
                    $this->initResponse(200, 'ad_failed');
                }
            }
        }

        if(!$failed){$this->initResponse(200, 'updating_settings_success');}
        return response()->json($this->data, 200);

    }

    public function insertAd($ad, $configuration_id){
        $newAd = new Setting;
        $newAd->configuration_id = $configuration_id;
        $newAd->value = $ad['url'];
        $newAd->save();
    }

    public function deleteAd($adId){
        $ad = Setting::find($adId);
        if(!$ad){return false;}
        $ad->delete();
        return true;
    }

    public function updateAd($ad){
        $updatedAd = Setting::find($ad['id']);
        if(!$updatedAd){return false;}
        $updatedAd->value = $ad['url'];
        $updatedAd->save();
        return true;
    }

    public function addConfiguration(Request $request){

        $attributes = $request->only('category');
        Configuration::create($attributes);
        $this->initResponse(200, 'add_config_success');
        return response()->json($this->data, 200);
    }


    /*******************************************************************************
     *********************************** Reports ***********************************
     *******************************************************************************/

    public function getReport(){
        
        if(!$report = Report::find($id)){$this->initResponse(400, 'get_report_fail');}
        else{$this->initResponse(200, 'get_report_success', $report);}
        return response()->json($this->data, 200);
    }
    
    public function getReports(){

        $reports = Report::all();
        $this->initResponse(200, 'get_reports_success', $reports);
        return response()->json($this->data, 200);
    }

    public function updateReport(UpdateReportRequest $request, $id){
        $attributes = $request->only('message', 'attachment');
        if(!$report = Report::find($id)){$this->initResponse(400, 'update_report_fail');}
        else{
            $report->update($attributes);
            $this->initResponse(200, 'update_report_success');
        }
        return response()->json($this->data, 200);
    }

    public function deleteReport($id){
        
        $report = Report::find($id);
        if(!$report){$this->initResponse(400, 'get_report_fail');}
        else{
            $report->delete();
            $this->initResponse(200, 'delete_report_success');
        }
        return response()->json($this->data, 200);
    }

    /*******************************************************************************
     *********************************** Vouchers **********************************
     *******************************************************************************/

    public function addVoucher(AddUpdateVoucherRequest $request){

        $attributes = $request->only('points', 'title', 'description', 'image');
        $voucher = Voucher::create($attributes);
        $this->initResponse(200, 'add_voucher_success');

        if($locales=$request->locales){
            if(!$this->storeLocales($locales, $voucher->id, Voucher::class)){
                $this->initResponse(400, 'store_locales_fail');
            }
        }

        return response()->json($this->data, 200);
    }

    public function updateVoucher(AddUpdateVoucherRequest $request, $id){
        
        $attributes = $request->only('value', 'points', 'title', 'description', 'deactivated');
        
        if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'update_voucher_fail');}
        else{
            $voucher->update($attributes);
            $this->initResponse(200, 'update_voucher_success');

            if($locales=$request->locales){
                if(!$this->updateLocales($locales, $voucher->id, Voucher::class)){
                    $this->initResponse(400, 'update_locales_fail');
                }
            }

        }
        return response()->json($this->data, 200);
    }

    public function deleteVoucher($id){
        
        if(!$voucher = Voucher::find($id)){$this->initResponse(400, 'get_voucher_fail');}
        else{
            if(!$this->deleteLocales($voucher->id, Voucher::class)){
                $this->initResponse(400, 'delete_locales_fail');
            }
            $voucher->delete();
            $this->initResponse(200, 'delete_voucher_success');
        }
        return response()->json($this->data, 200);
    }

}
