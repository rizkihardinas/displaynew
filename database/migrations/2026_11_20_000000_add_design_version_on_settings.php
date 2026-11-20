<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDesignVersionOnSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('settings', 'design_version')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('design_version')->default('v2');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('settings', 'design_version')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('design_version');
            });
        }
    }
}
