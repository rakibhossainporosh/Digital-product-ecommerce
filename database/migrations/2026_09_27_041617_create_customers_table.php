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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code', 32)->nullable()->unique();
            $table->foreignId('referred_by')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('whatsapp_number', 32)->nullable();
            $table->string('password')->nullable();
            $table->string('google_id')->nullable()->index();
            $table->string('facebook_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->boolean('is_reseller')->default(false);
            $table->decimal('reseller_discount', 5, 2)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_reseller']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
