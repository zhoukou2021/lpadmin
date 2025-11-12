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
        Schema::create('wa_admin_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('admin_id')->comment('管理员ID');
            $table->timestamps();
            
            $table->unique(['role_id', 'admin_id']);
            $table->foreign('role_id')->references('id')->on('wa_roles')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('wa_admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_admin_roles');
    }
};
