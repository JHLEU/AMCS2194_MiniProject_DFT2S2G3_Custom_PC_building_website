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
        // 1. Saved Custom PC Builds
        Schema::create('saved_builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('build_name', 100)->nullable();
            $table->string('customer_name', 100)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->json('components_json');
            $table->integer('total_wattage')->default(0);
            $table->decimal('estimated_price', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Chat Logs (for AI / Customer Service)
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('sender_type', ['Customer', 'Bot', 'Agent'])->default('Customer');
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
        Schema::dropIfExists('saved_builds');
    }
};
