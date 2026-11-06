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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('category', 100); // CPU, GPU, Motherboard, RAM, Storage, PSU, Case, Cooling
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('brand', 100)->nullable();
            $table->text('specs')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->integer('tdp_wattage')->default(0);
            $table->integer('performance_score')->default(0); // 0-100 performance rating score for CPU, GPU, RAM, Storage
            $table->string('socket_type', 50)->nullable(); // e.g. LGA1700, AM5, AM4
            $table->string('ram_type', 20)->nullable();    // e.g. DDR4, DDR5
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->decimal('selling_price', 10, 2)->default(0.00);
            $table->integer('warranty_months')->default(12);
            $table->integer('low_stock_threshold')->default(5);
            $table->timestamps();

            $table->index('category');
            $table->index('brand');
        });

        Schema::create('supplier_hardware_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('supplier_sku', 100)->nullable();
            $table->decimal('agreed_unit_price', 10, 2)->default(0.00);
            $table->integer('lead_time_days')->default(3);
            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_hardware_mappings');
        Schema::dropIfExists('products');
    }
};
