<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFfmpegSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('ip')->nullable();
            $table->string('ffmpeg_host')->nullable();
            $table->string('ffmpeg_port')->nullable();
            $table->string('ffmpeg_username')->nullable();
            $table->string('ffmpeg_password')->nullable();
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
            $table->dropColumn('ip');
            $table->dropColumn('ffmpeg_host');
            $table->dropColumn('ffmpeg_port');
            $table->dropColumn('ffmpeg_username');
            $table->dropColumn('ffmpeg_password');
        });
    }
}
