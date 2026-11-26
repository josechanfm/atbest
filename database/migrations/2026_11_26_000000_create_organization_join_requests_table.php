<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organization_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->index('status');
            $table->index('organization_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organization_join_requests');
    }
};
