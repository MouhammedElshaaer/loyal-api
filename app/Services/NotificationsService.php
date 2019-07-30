<?php

namespace App\Services;

use Illuminate\Http\Request;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

use App\Http\Traits\CRUDUtilities;

use App\Models\Device;

class NotificationsService
{
	use CRUDUtilities;

	private $notification;

	public function __construct(){

		$this->notification = [
			'title' => "",
			'body' => "",
			'data' => null,
		];

	}

	public function notify($tokens, $title, $body="", $data=null){

		$this->initNotification($title, $body, $data);
		$this->push($tokens);

	}

	protected function initNotification($title, $body, $data=null){

		$this->notification['title'] = $title;
		$this->notification['body'] = $body;
		$this->notification['data'] = $data;

	}

	protected function push($tokens){

		dump($this->notification);
		return true;

		$optionBuilder = new OptionsBuilder();
		$optionBuilder->setTimeToLive(60*20);

		$notificationBuilder = new PayloadNotificationBuilder($this->notification['title']);
		$notificationBuilder->setBody($this->notification['body'])
							->setSound('default');

		$dataBuilder = null;
		if ($this->notification['data']){
			$dataBuilder = new PayloadDataBuilder();
			$dataBuilder->addData(['a_data' => 'my_data']);
		}

		$option = $optionBuilder->build();
		$notification = $notificationBuilder->build();
		$data = $dataBuilder? $dataBuilder->build(): null;

		$downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

		$downstreamResponse->numberSuccess();
		$downstreamResponse->numberFailure();
		$downstreamResponse->numberModification();

		//return Array - you must remove all this tokens in your database
		$downstreamResponse->tokensToDelete();

		return true;

	}


	protected function removeTokens($tokens){
		foreach ($tokens as $token){
			if ($dataRow = $this->getDataRowByKey(Device::class, 'token', $token)) { $dataRow->delete; }
		}
	}

}