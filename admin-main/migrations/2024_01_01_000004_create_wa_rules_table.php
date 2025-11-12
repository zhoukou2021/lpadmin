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
        Schema::create('wa_rules', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->comment('规则标题');
            $table->string('icon', 50)->nullable()->comment('图标');
            $table->string('key', 100)->comment('规则标识');
            $table->unsignedBigInteger('pid')->default(0)->comment('父级规则ID');
            $table->string('href')->nullable()->comment('链接地址');
            $table->tinyInteger('type')->default(1)->comment('类型 0目录 1菜单 2权限');
            $table->integer('weight')->default(0)->comment('排序权重');
            $table->timestamps();
            
            $table->index(['pid', 'type']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_rules');
    }
};
