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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('duration_name', 100);
            $table->unsignedInteger('duration_days')->default(0);
            $table->decimal('regular_price', 10, 2)->unsigned();
            $table->decimal('offer_price', 10, 2)->unsigned()->nullable();
            $table->string('api_provider_id', 100)->nullable()->comment('External panel API package ID');
            $table->boolean('is_popular')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['product_id', 'duration_days']);
            $table->index(['product_id', 'is_popular']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
