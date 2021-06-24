<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDailyHealthStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_daily_health_statuses', function (Blueprint $table) {
            $table->tinyInteger('health_code_status');
            $table->string('health_code_status_details', 255)->nullable();
            $table->tinyInteger('vaccine_status');
            $table->tinyInteger('high_risk_region_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
