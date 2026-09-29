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
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 30)->default('digital_key')->after('category_id')->comment('digital_key or service');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('service_data')->nullable()->after('customer_notes')->comment('Stores dynamic service form fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('service_data');
        });
    }
};
