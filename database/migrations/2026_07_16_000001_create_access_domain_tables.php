<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('currency', 3)->default('XAF');
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price_minor');
            $table->unsignedInteger('validity_minutes');
            $table->unsignedInteger('download_limit_kbps')->nullable();
            $table->unsignedInteger('upload_limit_kbps')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained();
            $table->foreignId('plan_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->string('status')->default('pending');
            $table->unsignedInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('payment_reference')->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('plan_id')->constrained();
            $table->string('status')->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->unique()->constrained();
            $table->string('provider');
            $table->string('status')->default('pending');
            $table->string('external_reference')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_grants');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('sites');
    }
};
