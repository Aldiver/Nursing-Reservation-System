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
        Schema::create('dashboard_data', function (Blueprint $table) {
            $table->id();
            $table->date('week_start_date')->unique();
            $table->integer('pending_approvals_count')->default(0);
            $table->integer('recent_reservations_count')->default(0);
            $table->float('venue_utilization_rate', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_data');
    }
};
