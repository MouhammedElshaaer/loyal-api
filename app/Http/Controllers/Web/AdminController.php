<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\FetchSettingsRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\AddUpdateVoucherRequest;

use App\Http\Traits\ResponseUtilities;
use App\Http\Traits\CRUDUtilities;
use App\Http\Traits\LocaleUtilities;
use App\Http\Traits\SettingUtilities;

use App\Models\Report;
use App\Models\Voucher;
use App\Models\Configuration;
use App\Models\Setting;
use App\Models\Translation;
use App\Models\VoucherInstance;
use App\Models\Role;
use App\Models\ActionLog;
use App\User;

use App\Http\Resources\Setting as SettingResource;
use App\Http\Resources\ActionLog as ActionLogResource;

use Exception;

class AdminController extends Controller
{
    use ResponseUtilities, CRUDUtilities, LocaleUtilities, SettingUtilities;

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

        foreach($request->settings as $settingName=>$settingValue){
            
            $configuration = $this->getConfiguration(config('constants.settings.'.$settingName));
            $setting = $this->getSetting(config('constants.settings.'.$settingName));

            if (!$setting) { $setting = $this->createSetting(['configuration_id'=>$configuration->id]); }

            $setting->value = $settingValue;
            $setting->save();
            /**TODO: storing settings locals */
        }

        $failed = false;
        $adsConfig = $this->getConfiguration(config('constants.settings.ads'));
        foreach ($request->ads as $ad)
        {
            if ($ad["new_ad"]) {
                $this->createSetting(['configuration_id'=>$adsConfig->id, 'value'=>$ad['url']]);
            } else if ($ad["deleted"]) {
                if ($failed = !$this->deleteSetting($ad["id"])) { $this->initResponse(400, 'ad_failed'); }
            } else if ($ad["updated"]) {
                if ($failed = !$this->updateSetting($ad)) { $this->initResponse(400, 'ad_failed'); }
            }
        }

        if (!$failed) { $this->initResponse(200, 'updating_settings_success'); }
        return response()->json($this->data, 200);

    }

    public function settings(Request $request){

        $configs = config('constants.settings');
        unset($configs['ads']);

        $settings = $this->getSettings($configs);
        $ads = $this->getAds();

        $data = [
            'settings' => $settings,
            'ads' => $ads
        ];

        $this->initResponse(200, 'success', $data);
        return response()->json($this->data, 200);

    }

    public function addConfiguration(Request $request){

        $attributes = $request->only('category');
        $this->createConfiguration($attributes);
        $this->initResponse(200, 'add_config_success');
        return response()->json($this->data, 200);
    }


    /*******************************************************************************
     *********************************** Reports ***********************************
     *******************************************************************************/

    public function getReport(){
        
        if (!$report = Report::find($id)) { $this->initResponse(400, 'get_report_fail'); }
        else { $this->initResponse(200, 'get_report_success', $report); }
        return response()->json($this->data, 200);
    }
    
    public function getReports(){

        $reports = Report::all();
        $this->initResponse(200, 'get_reports_success', $reports);
        return response()->json($this->data, 200);
    }

    public function updateReport(UpdateReportRequest $request, $id){
        $attributes = $request->only('message', 'attachment');
        if (!$report = Report::find($id)) { $this->initResponse(400, 'update_report_fail'); }
        else {
            $report->update($attributes);
            $this->initResponse(200, 'update_report_success');
        }
        return response()->json($this->data, 200);
    }

    public function deleteReport($id){
        
        $report = Report::find($id);
        if (!$report) { $this->initResponse(400, 'get_report_fail'); }
        else {
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

        if ($locales=$request->locales) {
            if (!$this->storeLocales($locales, $voucher->id, Voucher::class)) {
                $this->initResponse(400, 'store_locales_fail');
            }
        }

        return response()->json($this->data, 200);
    }

    public function updateVoucher(AddUpdateVoucherRequest $request, $id){
        
        $attributes = $request->only('value', 'points', 'title', 'description', 'deactivated');
        
        if (!$voucher = Voucher::find($id)) { $this->initResponse(400, 'update_voucher_fail'); }
        else {
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
        
        if (!$voucher = Voucher::find($id)) { $this->initResponse(400, 'get_voucher_fail'); }
        else {
            if (!$this->deleteLocales($voucher->id, Voucher::class)) {
                $this->initResponse(400, 'delete_locales_fail');
            }
            $voucher->delete();
            $this->initResponse(200, 'delete_voucher_success');
        }
        return response()->json($this->data, 200);
    }

    /*******************************************************************************
     ********************************** Dashboard **********************************
     *******************************************************************************/

    public function dashboard(Request $request){



        $totalUsers = Role::where('name', 'customer')
                            ->first()
                            ->users()
                            ->where('deactivated', false)
                            ->get();

        $totalCashiers = Role::where('name', 'cashier')
                            ->first()
                            ->users()
                            ->where('deactivated', false)
                            ->get();

        $totalVoucherInstances = VoucherInstance::all();

        $dashboardContent = [
            'total_users' => $totalUsers->count(),
            'total_chashiers' => $totalCashiers->count(),
            'total_vouchers' => $totalVoucherInstances->count(),
        ];

        $this->initResponse(200, 'success', $dashboardContent);
        return response()->json($this->data, 200);

    }

    /*******************************************************************************
     ************************************ Users ************************************
     *******************************************************************************/

    public function createCashier(CreateUserRequest $request){

        $attributes = $request->only('country_code', 'phone', 'name', 'email', 'password', 'image');
        $attributes['password'] = bcrypt($attributes['password']);
        $user = $this->createUpdateDataRow(User::class, $attributes);

        if (!$user) { $this->initResponse(500, 'server_error'); }
        else {

            $user->verified = true;
            $user->save();

            if ($role = $this->getDataRowByKey(Role::class, 'name', 'cashier')) {
                $user->roles()->attach($role);
            }
        }

        $this->initResponse(200, 'success');
        return response()->json($this->data, 200);
    }

    public function updateUser(Request $request){

        $attributes = $request->all();
        $user = $this->createUpdateDataRow(User::class, $attributes);

        if (!$user) { $this->initResponse(500, 'server_error'); }

        $this->initResponse(200, 'success');
        return response()->json($this->data, 200);
    }

    public function deleteUser(Request $request, $id){

        if ($user= getDataRowByPrimaryKey(User::class, $id)) { $user->roles()->detach(); }
        if (!$this->deleteDataRow(User::class, $id)) { $this->initResponse(500, 'server_error'); }
        $this->initResponse(200, 'success');
        return response()->json($this->data, 200);
    }

    /*******************************************************************************
     ********************************* Action Logs *********************************
     *******************************************************************************/

    public function getActionLogs(Request $request){

        // $attributes = [];
        // return response()->json(['check' => array_key_exists('invoice_number', $attributes)]);

        return ActionLogResource::collection($this->getAllDataRows(ActionLog::class));

    }
}
