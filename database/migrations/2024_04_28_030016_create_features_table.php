<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id');
            $table->tinyInteger('sequence')->default(0);
            $table->string('title_zh');
            $table->string('title_en')->nullable();
            $table->string('title_pt')->nullable();
            $table->text('content_zh')->nullable();
            $table->text('content_en')->nullable();
            $table->text('content_pt')->nullable();
            $table->string('tags_zh')->nullable();
            $table->string('tags_en')->nullable();
            $table->string('tags_pt')->nullable();
            $table->string('image')->nullable();
            $table->string('url')->nullable();
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
        Schema::dropIfExists('features');
    }
};
