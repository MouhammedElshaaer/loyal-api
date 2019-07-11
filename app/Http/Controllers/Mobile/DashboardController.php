<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Traits\ResponseUtilities;

use Exception;

use App\Models\Report;
use App\User;

class DashboardController extends Controller
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
    
    public function getReport(){

        $report = null;
        try{

            $report = Report::find($id);
            if(!$report){$this->initResponse(400, 'get_report_fail');}
            else{
                $report->update[$attributes];
                $this->initResponse(400, 'get_report_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }
    
    public function getReports(){

        $reports = null;
        try{

            $reports = Report::all();
            $this->initResponse(200, 'get_reports_success', $reports);

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function addReport(Request $request){

        $attributes = $request->only('user_id', 'message', 'attachment');
        try{

            if( User::find($attributes['user_id']) ){
                $report = Report::create($attributes);
                $this->initResponse(200, 'add_reports_success');
            }else{throw new Exception();}

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }

    public function updateReport(Request $request, $id){

        try{

            $attributes = $request->only('message', 'attachement');
            $report = Report::find($id);
            if(!$report){$this->initResponse(400, 'update_report_fail');}
            else{
                $report->update[$attributes];
                $this->initResponse(400, 'update_report_success');
            }

        } catch (Exception $e) {$this->initErrorResponse($e);}
        return response()->json($this->data, 200);
    }
}
