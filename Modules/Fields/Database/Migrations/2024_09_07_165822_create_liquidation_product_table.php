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
        Schema::create('liquidation_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('liquidation_id')->constrained('liquidations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('category_product_id')->constrained('category_products')->onUpdate('cascade')->onDelete('cascade');

            $table->decimal('weight', total: 8, places: 2);
            $table->decimal('price', total: 8, places: 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidation_products');
    }
};
