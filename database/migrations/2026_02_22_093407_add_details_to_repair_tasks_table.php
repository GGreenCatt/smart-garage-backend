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
        Schema::table('repair_tasks', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('repair_order_id')->constrained('services')->nullOnDelete();
            $table->string('severity')->nullable()->after('customer_approval_status')->comment('low, medium, high');
            $table->text('description')->nullable()->after('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_tasks', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'severity', 'description']);
        });
    }
};
