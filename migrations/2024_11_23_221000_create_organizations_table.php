<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('type')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable();
            $table->json('contact_info')->nullable();
            $table->json('settings')->nullable();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id']);
            $table->index(['is_active']);
            $table->index(['slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
