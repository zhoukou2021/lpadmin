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
        Schema::create('role_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('rule_id')->comment('权限规则ID');
            $table->timestamps();

            $table->unique(['role_id', 'rule_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('rule_id')->references('id')->on('rules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_rules');
    }
};
