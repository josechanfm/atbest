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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('organization_id');
            $table->string('category_code');
            $table->integer('credit')->nullable();
            $table->string('title');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('tags')->nullable();
            $table->text('content')->nullable();
            $table->text('remark')->nullable();
            $table->date('valid_at')->nullable();
            $table->date('expire_at')->nullable();
            $table->boolean('require_login')->default(false);
            $table->boolean('for_member')->default(false);
            $table->boolean('published')->default(false);
            $table->boolean('with_attendance')->nullable();
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
        Schema::dropIfExists('events');
    }
};
