<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('vehicle_name')->nullable()->after('customer_id');
            $table->string('license_plate')->nullable()->after('vehicle_name');
            $table->text('reason')->nullable()->after('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['vehicle_name', 'license_plate', 'reason']);
        });
    }
};
