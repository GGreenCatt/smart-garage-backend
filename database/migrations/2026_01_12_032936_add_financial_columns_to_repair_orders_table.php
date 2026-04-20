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
        Schema::table('repair_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('diagnosis_note');
            }
            if (!Schema::hasColumn('repair_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            }
             if (!Schema::hasColumn('repair_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('discount_amount');
            }
             if (!Schema::hasColumn('repair_orders', 'promotion_id')) {
                $table->foreignId('promotion_id')->nullable()->after('tax_amount')->constrained('promotions')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            //
        });
    }
};
