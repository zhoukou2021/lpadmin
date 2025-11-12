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
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('system')->comment('配置分组');
            $table->string('name', 100)->unique()->comment('配置名称');
            $table->string('title', 100)->comment('配置标题');
            $table->text('value')->nullable()->comment('配置值');
            $table->string('type', 20)->default('text')->comment('配置类型');
            $table->text('options')->nullable()->comment('选项配置');
            $table->string('description')->nullable()->comment('描述');
            $table->integer('sort')->default(0)->comment('排序');
            $table->boolean('is_i18n')->default(false)->comment('是否多语言');
            $table->timestamps();

            $table->index(['group']);
            $table->index(['sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
