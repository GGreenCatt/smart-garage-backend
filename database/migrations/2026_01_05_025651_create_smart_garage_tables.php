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
        // Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->string('model'); // e.g. Toyota Camry
            $table->string('type')->default('sedan'); // sedan, suv, truck -> maps to 3D model
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('vin')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Owner (if registered)
            $table->string('owner_phone')->index(); // Critical for guest lookups
            $table->string('owner_name')->nullable();
            $table->timestamps();
        });

        // Jobs (Service Records) -> Renamed to Repair Orders
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('track_id')->unique()->comment('RO-YYYYMMDD-XXX');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->integer('odometer_reading')->nullable();
            $table->text('diagnosis_note')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('expected_completion_date')->nullable();
            
            // Legacy/Optional fields
            $table->string('service_type')->nullable();
            $table->integer('progress')->default(0);
            $table->timestamp('start_time')->nullable();
            $table->timestamps();
        });

        // Job Tasks (Checklist items)
        Schema::create('repair_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained('repair_orders')->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('pending'); // pending, in_progress, done
            $table->timestamps();
        });

        // VHC Reports (3D Data)
        Schema::create('vhc_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->constrained('repair_orders')->cascadeOnDelete();
            $table->timestamps();
        });

        // VHC Defects (Points on 3D Model)
        Schema::create('vhc_defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vhc_report_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // exterior, interior, engine, tires
            $table->string('severity')->default('medium'); // low, medium, high
            $table->float('pos_x');
            $table->float('pos_y');
            $table->float('pos_z');
            $table->json('images')->nullable(); // ["url1", "url2"]
            $table->timestamps();
        });

        // Chat Sessions (Contextual Support)
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_order_id')->nullable()->constrained('repair_orders')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('guest_session_id')->nullable()->index(); // For non-logged in users
            $table->string('status')->default('open');
            $table->timestamps();
        });

        // Chat Messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete(); // Null if guest
            $table->boolean('is_staff')->default(false);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_garage_tables');
    }
};
