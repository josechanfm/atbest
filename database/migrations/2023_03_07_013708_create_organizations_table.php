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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('parish')->nullable();
            $table->string('abbr');
            $table->string('name_display')->nullable();
            $table->string('name_zh')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_pt')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('href')->nullable();
            $table->string('avatar')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('registration_code')->nullable();
            $table->string('president')->nullable();
            $table->string('card_style')->nullable();
            $table->string('logo')->nullable();
            $table->string('subdomain')->nullable();
            $table->date('founted_at')->nullable();
            $table->boolean('status')->nullable();
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
        Schema::dropIfExists('organizations');
    }
};
