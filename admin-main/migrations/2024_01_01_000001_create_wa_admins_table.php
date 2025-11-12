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
        Schema::create('wa_admins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique()->comment('用户名');
            $table->string('nickname', 40)->comment('昵称');
            $table->string('password')->comment('密码');
            $table->string('avatar')->default('/admin/avatar.png')->comment('头像');
            $table->string('email', 100)->nullable()->comment('邮箱');
            $table->string('mobile', 16)->nullable()->comment('手机');
            $table->timestamp('login_at')->nullable()->comment('登录时间');
            $table->tinyInteger('status')->default(1)->comment('状态 1正常 0禁用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_admins');
    }
};
