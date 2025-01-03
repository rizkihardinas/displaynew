<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->id();
            $table->string('background_color')->default('#ffffff');
            $table->string('primary_color')->default('#04427b');
            $table->string('text_primary_color')->default('#ffffff');
            $table->string('secondary_color')->default('#f1ff00');
            $table->string('text_secondary_color')->default('#000000');
            $table->string('header_color')->default('#94ceff');
            $table->string('footer_color')->default('#94ceff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preferences');
    }
}
