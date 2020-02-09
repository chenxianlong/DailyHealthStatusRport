<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWechatWorkApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_work_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->string('agent_id', 16);
            $table->string('secret');
            $table->string('name');
            $table->string('access_token', 512)->nullable();
            $table->unsignedInteger('access_token_expire_at')->default(0);
            $table->timestamps();

            $table->unique(["account_id", "agent_id"]);

            $table
                ->foreign("account_id")
                ->references("id")
                ->on("wechat_work_account")
                ->onUpdate("cascade")
                ->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_work_applications');
    }
}
