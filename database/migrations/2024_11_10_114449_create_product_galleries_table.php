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
        Schema::create('product_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // ID sản phẩm
            $table->string('url'); // URL của ảnh
            $table->timestamps(); // created_at và updated_at

            // Thêm khóa ngoại (optional)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_galleries');
    }
};
