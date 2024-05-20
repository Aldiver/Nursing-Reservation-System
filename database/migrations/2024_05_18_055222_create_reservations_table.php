<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('number_of_participants')->nullable();
            $table->boolean('isNoted')->default(false);
            $table->boolean('isApproved')->default(false);
            $table->foreignId('user_id')->on('users');
            $table->foreignId('venue_id')->on('venues');
            $table->foreignId('noted_by')->on('users')->default(null);
            $table->foreignId('approved_by')->on('users')->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
