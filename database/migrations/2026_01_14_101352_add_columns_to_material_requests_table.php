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
        Schema::table('material_requests', function (Blueprint $table) {
            $table->foreignId('repair_order_id')->nullable()->constrained('repair_orders')->cascadeOnDelete();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0); // Selling Price
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropForeign(['repair_order_id']);
            $table->dropColumn(['repair_order_id', 'cost_price', 'unit_price']);
        });
    }
};
