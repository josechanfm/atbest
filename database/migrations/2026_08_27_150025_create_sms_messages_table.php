<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // who sent it (if admin)
            $table->string('mobile', 20); // recipient phone number
            $table->text('content'); // message text
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->json('response')->nullable(); // raw API response
            $table->timestamp('sent_at')->nullable(); // when it was actually sent
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('sent_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_messages');
    }
};