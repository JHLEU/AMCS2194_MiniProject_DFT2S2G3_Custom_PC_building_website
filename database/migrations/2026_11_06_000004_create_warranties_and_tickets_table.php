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
        // 1. Warranties linked to sold serial number & order
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serial_number_id')->constrained('serial_numbers')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Active', 'Expired', 'Void'])->default('Active');
            $table->timestamps();
        });

        // 2. Customer Support Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warranty_id')->nullable()->constrained('warranties')->nullOnDelete();
            $table->string('subject', 200)->nullable();
            $table->text('issue_description');
            $table->enum('status', ['Open', 'Diagnosing', 'WaitingForParts', 'Resolved', 'Closed'])->default('Open');
            $table->timestamps();
        });

        // 3. Technician After-Sales Dispatch & Schedules
        Schema::create('tech_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('scheduled_datetime');
            $table->integer('estimated_duration_hours')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tech_schedules');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('warranties');
    }
};
