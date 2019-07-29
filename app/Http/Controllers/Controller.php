<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\NotificationsService;

use App\Http\Traits\ResponseUtilities;
use App\Http\Traits\CRUDUtilities;
use App\Http\Traits\CodeGenerationUtilities;
use App\Http\Traits\LocaleUtilities;
use App\Http\Traits\SettingUtilities;
use App\Http\Traits\LogUtilities;
use App\Http\Traits\StatusUtilities;

use Exception;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests,
        ResponseUtilities,
        CRUDUtilities,
        CodeGenerationUtilities,
        LocaleUtilities,
        SettingUtilities,
        LogUtilities,
        StatusUtilities;


    protected $data;
    protected $notificationsService;

    public function __construct(NotificationsService $_notificationsService){

        $this->data = [
            "code"=> null,
            "message"=>"",
            "data" => new \stdClass()
        ];

        $this->notificationsService = $_notificationsService;

    }
}
