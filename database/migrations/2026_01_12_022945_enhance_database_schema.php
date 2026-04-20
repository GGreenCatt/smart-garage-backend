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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });

        Schema::table('repair_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_orders', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            }
            if (!Schema::hasColumn('repair_orders', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
            if (!Schema::hasColumn('repair_orders', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
        
        // Safety check for vehicles table as well
        Schema::table('vehicles', function (Blueprint $table) {
             if (!Schema::hasColumn('vehicles', 'color')) {
                $table->string('color')->nullable();
             }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'status', 'avatar']);
        });

        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'notes']);
        });
        
         Schema::table('vehicles', function (Blueprint $table) {
            //$table->dropColumn(['color']); // Avoid dropping if it existed before
        });
    }
};
