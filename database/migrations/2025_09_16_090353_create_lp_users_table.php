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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->comment('用户名');
            $table->string('password')->comment('密码');
            $table->string('nickname', 50)->comment('昵称');
            $table->string('email', 100)->nullable()->unique()->comment('邮箱');
            $table->string('phone', 20)->nullable()->unique()->comment('手机号');
            $table->string('avatar')->nullable()->comment('头像');
            $table->tinyInteger('gender')->default(0)->comment('性别 0:未知 1:男 2:女');
            $table->date('birthday')->nullable()->comment('生日');
            $table->tinyInteger('status')->default(1)->comment('状态 1:启用 0:禁用');
            $table->text('remark')->nullable()->comment('备注');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->string('last_login_ip', 45)->nullable()->comment('最后登录IP');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['gender']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
