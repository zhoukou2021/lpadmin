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
        // 管理员表
        Schema::create('wa_admins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique()->comment('用户名');
            $table->string('nickname', 40)->comment('昵称');
            $table->string('password')->comment('密码');
            $table->string('avatar')->default('/app/admin/avatar.png')->comment('头像');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('mobile', 16)->nullable()->comment('手机');
            $table->timestamp('login_at')->nullable()->comment('登录时间');
            $table->tinyInteger('status')->default(1)->comment('状态：1启用，0禁用');
            $table->timestamps();
            
            $table->index(['username', 'status']);
        });

        // 角色表
        Schema::create('wa_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('角色名称');
            $table->text('rules')->nullable()->comment('权限规则');
            $table->unsignedBigInteger('pid')->default(0)->comment('父级角色ID');
            $table->timestamps();
            
            $table->index(['pid']);
        });

        // 管理员角色关联表
        Schema::create('wa_admin_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('admin_id')->comment('管理员ID');
            $table->timestamps();
            
            $table->unique(['role_id', 'admin_id']);
            $table->foreign('role_id')->references('id')->on('wa_roles')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('wa_admins')->onDelete('cascade');
        });

        // 权限规则表
        Schema::create('wa_rules', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->comment('规则标题');
            $table->string('icon', 50)->nullable()->comment('图标');
            $table->string('key', 100)->comment('规则标识');
            $table->unsignedBigInteger('pid')->default(0)->comment('父级规则ID');
            $table->string('href')->nullable()->comment('链接地址');
            $table->tinyInteger('type')->default(1)->comment('类型：0目录，1菜单，2权限');
            $table->integer('weight')->default(0)->comment('排序权重');
            $table->timestamps();
            
            $table->index(['pid', 'type']);
            $table->index(['key']);
        });

        // 系统配置表
        Schema::create('wa_options', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique()->comment('配置键');
            $table->longText('value')->comment('配置值');
            $table->timestamps();
        });

        // 用户表
        Schema::create('wa_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique()->comment('用户名');
            $table->string('nickname', 40)->comment('昵称');
            $table->string('password')->comment('密码');
            $table->tinyInteger('sex')->default(0)->comment('性别：0未知，1男，2女');
            $table->string('avatar')->default('/app/admin/avatar.png')->comment('头像');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('mobile', 16)->nullable()->comment('手机');
            $table->integer('level')->default(1)->comment('等级');
            $table->date('birthday')->nullable()->comment('生日');
            $table->decimal('money', 10, 2)->default(0)->comment('余额');
            $table->integer('score')->default(0)->comment('积分');
            $table->timestamp('last_time')->nullable()->comment('最后登录时间');
            $table->string('last_ip', 45)->nullable()->comment('最后登录IP');
            $table->timestamp('join_time')->nullable()->comment('注册时间');
            $table->string('join_ip', 45)->nullable()->comment('注册IP');
            $table->string('token')->nullable()->comment('登录令牌');
            $table->tinyInteger('status')->default(1)->comment('状态：1启用，0禁用');
            $table->timestamps();
            
            $table->index(['username', 'status']);
            $table->index(['email']);
            $table->index(['mobile']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_admin_roles');
        Schema::dropIfExists('wa_users');
        Schema::dropIfExists('wa_options');
        Schema::dropIfExists('wa_rules');
        Schema::dropIfExists('wa_roles');
        Schema::dropIfExists('wa_admins');
    }
};
