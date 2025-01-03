<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeoutOnSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->double('timeout_in')->after('duration')->default(30)->comment('Timeout for each question in seconds');
            $table->double('timeout_out')->after('timeout_in')->default(30)->comment('Timeout for each question in seconds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('timeout_in');
            $table->dropColumn('timeout_out');
        });
    }
}
