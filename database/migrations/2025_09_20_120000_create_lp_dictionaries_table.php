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
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('字典名称（唯一标识）');
            $table->string('title', 200)->comment('字典标题');
            $table->string('type', 50)->default('select')->comment('字典类型：select=下拉选择，radio=单选，checkbox=多选');
            $table->text('description')->nullable()->comment('字典描述');
            $table->integer('sort')->default(0)->comment('排序权重');
            $table->tinyInteger('status')->default(1)->comment('状态：0=禁用，1=启用');
            $table->timestamps();
            
            // 索引
            $table->unique('name', 'dictionaries_name_unique');
            $table->index('status', 'dictionaries_status_index');
            $table->index('sort', 'dictionaries_sort_index');
            $table->index('type', 'dictionaries_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
    }
};
