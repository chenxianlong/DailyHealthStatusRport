<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableExportUserIdWhiteListsAddColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("export_user_id_white_lists", function (Blueprint $table) {
            $table->dropForeign(["user_id"]);
            $table->dropPrimary();
        });
        Schema::table("export_user_id_white_lists", function (Blueprint $table) {
            $table->tinyInteger("type")->default(1);
            $table->foreign("user_id")->on("users")->references("id")->onUpdate("cascade")->onDelete("cascade");
            $table->primary(["user_id", "type"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("export_user_id_white_lists", function (Blueprint $table) {
            $table->dropForeign(["user_id"]);
            $table->dropPrimary();
        });
        Schema::table("export_user_id_white_lists", function (Blueprint $table) {
            $table->dropColumn("type");
            $table->foreign("user_id")->on("users")->references("id")->onUpdate("cascade")->onDelete("cascade");
            $table->primary("user_id");
        });
    }
}
