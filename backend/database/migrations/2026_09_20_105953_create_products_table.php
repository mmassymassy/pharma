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
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('buy_price', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->enum('availability', ['in_stock', 'on_request', 'unavailable'])->default('in_stock');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
