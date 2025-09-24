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
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父级ID');
            $table->string('name', 100)->unique()->comment('权限名称');
            $table->string('title', 100)->comment('权限标题');
            $table->enum('type', ['menu', 'api', 'button'])->default('menu')->comment('类型：menu菜单,api接口,button按钮');
            $table->string('icon', 50)->nullable()->comment('图标');
            $table->string('route_name', 100)->nullable()->comment('路由名称');
            $table->string('url')->nullable()->comment('菜单URL');
            $table->string('component')->nullable()->comment('前端组件');
            $table->string('target', 20)->default('_self')->comment('打开方式');
            $table->tinyInteger('is_show')->default(1)->comment('是否显示：0隐藏,1显示');
            $table->string('menu_name', 100)->nullable()->comment('关联菜单标识');
            $table->tinyInteger('status')->default(1)->comment('状态 1:启用 0:禁用');
            $table->integer('sort')->default(0)->comment('排序');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();

            // 索引优化
            $table->index(['parent_id', 'sort']);
            $table->index(['type', 'is_show', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
