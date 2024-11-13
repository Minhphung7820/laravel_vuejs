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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID của người dùng gửi tin nhắn
            $table->unsignedBigInteger('conversation_id'); // ID của cuộc hội thoại
            $table->text('message'); // Nội dung tin nhắn
            $table->timestamps();

            // Thiết lập khoá ngoại (nếu có các bảng users và conversations)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
