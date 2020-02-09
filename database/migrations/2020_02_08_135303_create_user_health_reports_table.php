<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserHealthReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_health_reports', function (Blueprint $table) {
            $table->timestamp('reported_date');
            $table->unsignedBigInteger('user_id');
            $table->string('field', 191);
            $table->text('value')->nullable();

            $table->unique(['reported_date', 'user_id', 'field']);
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
        Schema::dropIfExists('user_health_reports');
    }
}
