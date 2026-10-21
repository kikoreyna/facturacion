<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_tariff_list', function (Blueprint $table) {
            $table->string('currency', 3)->default('MXN')->after('price');
        });

        Schema::table('service_order_items', function (Blueprint $table) {
            $table->string('currency', 3)->default('MXN')->after('unit_price');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency', 3)->default('MXN')->after('tariff_list_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $t) => $t->dropColumn('currency'));
        Schema::table('service_order_items', fn (Blueprint $t) => $t->dropColumn('currency'));
        Schema::table('service_tariff_list', fn (Blueprint $t) => $t->dropColumn('currency'));
    }
};
