<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('servicio'); // hora, servicio, mes...
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tarifa de cada servicio por tabulador
        Schema::create('service_tariff_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_list_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->unique(['service_id', 'tariff_list_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tariff_list');
        Schema::dropIfExists('services');
    }
};
