<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDailyHealthStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_daily_health_statuses', function (Blueprint $table) {
            $table->timestamp('reported_date');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('self_status');
            $table->string('self_status_details', 255)->nullable();
            $table->tinyInteger('family_status');
            $table->string('family_status_details', 255)->nullable();

            $table->primary(['reported_date', 'user_id']);
            $table->foreign("user_id")->on("users")->references("id")->onUpdate("cascade")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_daily_health_statuses');
    }
}
