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
        Schema::table('internal_messages', function (Blueprint $table) {
            $table->bigInteger('receiver_id')->unsigned()->nullable()->change();
            $table->boolean('is_group')->default(true)->after('receiver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_messages', function (Blueprint $table) {
            $table->bigInteger('receiver_id')->unsigned()->nullable(false)->change();
            $table->dropColumn('is_group');
        });
    }
};
