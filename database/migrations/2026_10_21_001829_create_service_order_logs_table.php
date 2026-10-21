<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bitácora cronológica de cada orden
        Schema::create('service_order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('show_on_invoice')->default(false);
            $table->timestamps();
            $table->index(['service_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_logs');
    }
};
