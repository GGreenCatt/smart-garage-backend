<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_orders', 'quote_sent_at')) {
                $table->timestamp('quote_sent_at')->nullable()->after('quote_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            if (Schema::hasColumn('repair_orders', 'quote_sent_at')) {
                $table->dropColumn('quote_sent_at');
            }
        });
    }
};
