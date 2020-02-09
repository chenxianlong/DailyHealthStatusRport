<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserHealthCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_health_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('phone');
            $table->string('address', 512);
            $table->timestamp('in_key_places_from')->nullable();
            $table->timestamp('in_key_places_to')->nullable();
            $table->timestamp('back_to_dongguan_at')->nullable();
            $table->timestamp('touched_high_risk_people_at')->nullable();
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
        Schema::dropIfExists('user_health_cards');
    }
}
