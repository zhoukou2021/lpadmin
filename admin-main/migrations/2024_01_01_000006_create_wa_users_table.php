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
        Schema::create('wa_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique()->comment('用户名');
            $table->string('nickname', 40)->comment('昵称');
            $table->string('password')->comment('密码');
            $table->tinyInteger('sex')->default(0)->comment('性别 0未知 1男 2女');
            $table->string('avatar')->default('/admin/avatar.png')->comment('头像');
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
            $table->tinyInteger('status')->default(1)->comment('状态 1正常 0禁用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_users');
    }
};
