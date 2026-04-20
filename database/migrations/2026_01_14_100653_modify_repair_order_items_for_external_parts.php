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
        Schema::table('repair_order_items', function (Blueprint $table) {
            $table->string('itemable_type')->nullable()->change();
            $table->unsignedBigInteger('itemable_id')->nullable()->change();
            $table->string('name')->nullable()->after('repair_order_id'); // Custom name
            $table->decimal('cost_price', 12, 2)->default(0)->after('unit_price'); // Import price
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_order_items', function (Blueprint $table) {
            $table->string('itemable_type')->nullable(false)->change();
            $table->unsignedBigInteger('itemable_id')->nullable(false)->change();
            $table->dropColumn(['name', 'cost_price']);
        });
    }
};
