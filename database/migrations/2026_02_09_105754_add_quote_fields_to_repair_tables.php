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
            if (!Schema::hasColumn('repair_tasks', 'labor_cost')) {
                $table->decimal('labor_cost', 12, 2)->default(0)->after('status');
            }
        });

        Schema::table('repair_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_order_items', 'repair_task_id')) {
                $table->foreignId('repair_task_id')->nullable()->after('repair_order_id')->constrained('repair_tasks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('repair_order_items', 'repair_task_id')) {
                $table->dropForeign(['repair_task_id']);
                $table->dropColumn('repair_task_id');
            }
        });

        Schema::table('repair_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('repair_tasks', 'labor_cost')) {
                $table->dropColumn('labor_cost');
            }
        });
    }
};
