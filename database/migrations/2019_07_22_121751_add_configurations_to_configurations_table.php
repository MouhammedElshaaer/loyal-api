<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Http\Traits\SettingUtilities;

class AddConfigurationsToConfigurationsTable extends Migration
{
    use SettingUtilities;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $settings = config('constants.settings');

        foreach($settings as $setting){
            \DB::table('configurations')->insert(['category' => $setting]);
        }

        /**Adding basic settings defaults*/

        $defaultSettings = config('constants.default_settings');

        foreach($defaultSettings as $settingName=>$settingValue){
            
            $configuration = $this->getConfiguration(config('constants.'.$settingName));
            $setting = $this->getSetting(config('constants.'.$settingName));

            if (!$setting) { $setting = $this->createSetting(['configuration_id'=>$configuration->id]); }

            $setting->value = $settingValue;
            $setting->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('settings')->truncate();

        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign('settings_configuration_id_foreign');
        });

        \DB::table('configurations')->truncate();

        Schema::table('settings', function (Blueprint $table) {
            $table->foreign('configuration_id')->references('id')->on('configurations');
        });
    }
}
