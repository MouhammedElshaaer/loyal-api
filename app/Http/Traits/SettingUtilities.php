<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\Configuration;
use App\Models\Setting;

trait SettingUtilities
{

    public function createSetting($attributes){
        return Setting::create($attributes);
    }

    public function getSetting($configName){
        $setting = null;
        if ($configuration = $this->getConfiguration($configName)) {
            $setting = Setting::where('configuration_id', $configuration->id)->first();
        }
        return $setting;
    }

    public function getSettings($configName){
        $setting = null;
        if ($configuration = $this->getConfiguration($configName)) {
            $setting = Setting::where('configuration_id', $configuration->id)->get();
        }
        return $setting;
    }

    public function deleteSetting($id){
        $setting = Setting::find($id);
        if ($setting) { $setting->delete(); }
        return $setting;
    }

    public function updateSetting($setting){
        $updatedSetting = Setting::find($setting['id']);
        if ($updatedSetting) {
            $updatedSetting->value = $setting['url'];
            $updatedSetting->save();
        }
        return $updatedSetting;
    }

    public function createConfiguration($attributes){
        return Configuration::create($attributes);
    }

    public function getConfiguration($configName){
        return Configuration::where('category', $configName)->first();
    }
}