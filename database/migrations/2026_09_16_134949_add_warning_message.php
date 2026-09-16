<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarningMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('warning_message')->nullable();
            $table->string('warning_background')->default('red')->nullable();
            $table->integer('warning_font_size')->default(12)->nullable();
            $table->string('warning_font_color')->default('yellow')->nullable();
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
            $table->dropColumn('warning_message');
            $table->dropColumn('warning_background');
            $table->dropColumn('warning_font_size');
            $table->dropColumn('warning_font_color');
        });
    }
}
