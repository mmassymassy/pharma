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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('request_received');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('delivery_name');
            $table->string('delivery_phone');
            $table->string('delivery_country')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_address');
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->enum('source', ['website', 'whatsapp', 'manual'])->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
