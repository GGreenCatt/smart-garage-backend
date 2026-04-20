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
        Schema::table('sos_requests', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('sos_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('guest_name')->nullable()->after('customer_id');
            $table->string('guest_phone')->nullable()->after('guest_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sos_requests', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_phone']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('sos_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
