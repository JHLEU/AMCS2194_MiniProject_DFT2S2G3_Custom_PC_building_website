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
        // 1. Restock Orders from Suppliers
        Schema::create('restock_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->enum('status', ['Pending', 'Approved', 'Completed', 'Cancelled'])->default('Pending');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('restock_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_order_id')->constrained('restock_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Customer Orders & Payments
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('order_number', 50)->unique();
            $table->decimal('total_selling_amount', 10, 2)->default(0.00);
            $table->decimal('total_cost_amount', 10, 2)->default(0.00);
            $table->enum('order_status', [
                'Pending', 'Paid', 'Assembling', 'Testing', 'Shipped', 'Completed', 'Cancelled'
            ])->default('Pending');
            $table->string('tracking_number', 100)->nullable();
            $table->string('carrier', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('payment_method', 50)->nullable(); // e.g. FPX, Card, Sandbox
            $table->string('transaction_id', 100)->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_status', ['Pending', 'Success', 'Failed'])->default('Pending');
            $table->dateTime('payment_date')->nullable();
            $table->timestamps();
        });

        // 3. Serial Numbers Tracking
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('sn_code', 100)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('status', ['In-Stock', 'Reserved', 'Sold', 'RMA', 'Lost'])->default('In-Stock');
            $table->decimal('batch_cost', 10, 2)->default(0.00);
            $table->foreignId('restock_order_id')->nullable()->constrained('restock_orders')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('restock_order_items');
        Schema::dropIfExists('restock_orders');
    }
};
