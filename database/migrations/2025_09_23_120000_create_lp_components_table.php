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
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('组件名称');
            $table->string('title', 200)->comment('组件标题');
            $table->text('description')->nullable()->comment('组件描述');
            $table->string('version', 20)->default('1.0.0')->comment('组件版本');
            $table->string('author', 100)->nullable()->comment('组件作者');
            $table->json('config')->nullable()->comment('组件配置');
            $table->tinyInteger('status')->default(0)->comment('状态 1:已安装 0:未安装');
            $table->timestamp('installed_at')->nullable()->comment('安装时间');
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
