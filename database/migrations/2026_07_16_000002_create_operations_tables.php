<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_grants', function (Blueprint $table) {
            $table->timestamp('revoked_at')->nullable()->after('authorized_at');
            $table->text('last_error')->nullable()->after('revoked_at');
        });

        Schema::create('subscription_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('reason');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['subscription_id', 'created_at']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint')->unique();
            $table->string('type');
            $table->string('severity')->default('warning');
            $table->string('status')->default('open');
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('subscription_transitions');

        Schema::table('access_grants', function (Blueprint $table) {
            $table->dropColumn(['revoked_at', 'last_error']);
        });
    }
};
