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
            $table->string('quote_status')->default('draft')->after('status'); // draft, sent, approved, rejected
        });

        Schema::table('repair_items', function (Blueprint $table) {
            $table->string('status')->default('approved')->after('price'); // pending, approved, rejected
            // Defaulting to 'approved' to avoid breaking existing flow if needed, 
            // but for Module 4 we'll work with 'pending' for new items.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropColumn('quote_status');
        });

        Schema::table('repair_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
