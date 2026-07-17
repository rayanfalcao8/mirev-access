<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider')->default('fake');
            $table->text('configuration')->nullable();
            $table->string('status')->default('unconfigured');
            $table->timestamp('last_tested_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_connections');
    }
};
