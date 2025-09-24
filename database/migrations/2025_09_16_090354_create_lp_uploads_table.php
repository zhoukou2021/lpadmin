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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->comment('上传者ID');
            $table->string('original_name')->comment('原始文件名');
            $table->string('filename')->comment('存储文件名');
            $table->string('path')->comment('文件路径');
            $table->string('url')->comment('访问URL');
            $table->string('mime_type', 100)->comment('MIME类型');
            $table->unsignedBigInteger('size')->comment('文件大小(字节)');
            $table->string('extension', 10)->comment('文件扩展名');
            $table->string('disk', 20)->default('public')->comment('存储磁盘');
            $table->string('hash', 64)->nullable()->comment('文件哈希值');
            $table->string('category', 50)->default('general')->comment('文件分类');
            $table->json('tags')->nullable()->comment('文件标签');
            $table->text('description')->nullable()->comment('文件描述');
            $table->json('metadata')->nullable()->comment('元数据');
            $table->timestamps();

            $table->index(['admin_id']);
            $table->index(['mime_type']);
            $table->index(['extension']);
            $table->index(['category']);
            $table->index(['created_at']);
            $table->index(['hash']);

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
