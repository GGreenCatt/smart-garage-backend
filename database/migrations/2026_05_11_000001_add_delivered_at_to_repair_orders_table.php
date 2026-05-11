<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('repair_orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            if (Schema::hasColumn('repair_orders', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
        });
    }
};
