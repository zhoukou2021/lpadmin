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
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->comment('管理员ID');
            $table->string('admin_username', 50)->comment('管理员用户名');
            $table->string('action', 20)->comment('操作类型');
            $table->string('module', 50)->comment('模块名称');
            $table->string('route_name', 100)->nullable()->comment('路由名称');
            $table->string('method', 10)->comment('请求方法');
            $table->text('url')->comment('请求URL');
            $table->string('ip', 45)->comment('IP地址');
            $table->text('user_agent')->nullable()->comment('用户代理');
            $table->longText('request_data')->nullable()->comment('请求数据');
            $table->integer('response_code')->comment('响应状态码');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');

            $table->index(['admin_id']);
            $table->index(['action']);
            $table->index(['module']);
            $table->index(['ip']);
            $table->index(['created_at']);
            $table->index(['response_code']);

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
