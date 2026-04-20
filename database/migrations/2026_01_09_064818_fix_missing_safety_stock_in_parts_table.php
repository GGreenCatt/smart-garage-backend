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
        if (!Schema::hasColumn('parts', 'safety_stock')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->integer('safety_stock')->default(5)->after('min_stock')->comment('Reorder Point / Warning Level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('parts', 'safety_stock')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dropColumn('safety_stock');
            });
        }
    }
};
