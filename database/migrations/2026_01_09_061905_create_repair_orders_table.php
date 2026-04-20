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
        if (!Schema::hasTable('repair_orders')) {
            Schema::create('repair_orders', function (Blueprint $table) {
                $table->id();
                $table->string('track_id')->unique()->comment('RO-YYYYMMDD-XXX');
                $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
                $table->foreignId('advisor_id')->constrained('users'); 
                $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->integer('odometer_reading')->nullable();
                $table->text('diagnosis_note')->nullable();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->dateTime('expected_completion_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
