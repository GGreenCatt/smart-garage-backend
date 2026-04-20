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
            $table->foreignId('parent_id')->nullable()->constrained('repair_tasks')->onDelete('cascade');
            $table->string('type')->default('general'); // vhc, electrical, engine, general
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'type']);
        });
    }
};
