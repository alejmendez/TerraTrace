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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('description', 255)->nullable();
            $table->string('version', 20)->default('1.0.0');
            $table->string('author', 100)->nullable();
            $table->string('author_email', 100)->nullable();
            $table->string('author_url', 100)->nullable();
            $table->string('license', 100)->nullable();
            $table->string('license_url', 100)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
