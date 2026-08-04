<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFontDetailOutSettingOnPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if(!Schema::hasColumn('settings', 'delay_in')) {
                $table->integer('delay_in')->nullable();
            }else{
                $table->tinyInteger('delay_in')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preferences', function (Blueprint $table) {
            $table->dropColumn('delay_in');
        });
    }
}
