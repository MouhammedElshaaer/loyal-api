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

        $attributesArray = [
            ['category' => __('constants.pending_duration')],
            ['category' => __('constants.premium_pending_duration')],
            ['category' => __('constants.valid_duration')],
            ['category' => __('constants.premium_valid_duration')],
            ['category' => __('constants.points_per_currency_unit')],
            ['category' => __('constants.premium_points_per_currency_unit')],
            ['category' => __('constants.currency_unit')],
            ['category' => __('constants.premium_currency_unit')],
            ['category' => __('constants.premium_threshold')],
            ['category' => __('constants.policies')],
            ['category' => __('constants.ads')]
        ];

        foreach($attributesArray as $attributes){
            \DB::table('configurations')->insert($attributes);
        }

        /**Adding basic settings defaults*/

        $settings = [
            "valid_duration" => "5",
            "premium_valid_duration" => "7",
            "pending_duration" => "1",
            "premium_pending_duration" => "0",
            "points_per_currency_unit" => "4",
            "premium_points_per_currency_unit" => "8",
            "currency_unit" => "1",
            "premium_currency_unit" => "1",
            "premium_threshold" => "1000",
            "policies" => "new may be simple text or html markup"
        ];

        foreach($settings as $settingName=>$settingValue){
            
            $configuration = $this->getConfiguration(__('constants.'.$settingName));
            $setting = $this->getSetting(__('constants.'.$settingName));

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
