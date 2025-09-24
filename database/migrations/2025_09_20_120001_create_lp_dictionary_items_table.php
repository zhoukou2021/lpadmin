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
        Schema::create('dictionary_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dictionary_id')->comment('字典ID');
            $table->string('label', 200)->comment('显示标签');
            $table->string('value', 200)->comment('选项值');
            $table->string('color', 20)->nullable()->comment('颜色标识');
            $table->text('description')->nullable()->comment('选项描述');
            $table->integer('sort')->default(0)->comment('排序权重');
            $table->tinyInteger('status')->default(1)->comment('状态：0=禁用，1=启用');
            $table->timestamps();
            
            // 外键约束
            $table->foreign('dictionary_id', 'dictionary_items_dictionary_id_foreign')
                  ->references('id')->on('dictionaries')->onDelete('cascade');
            
            // 索引
            $table->index('dictionary_id', 'dictionary_items_dictionary_id_index');
            $table->index('status', 'dictionary_items_status_index');
            $table->index('sort', 'dictionary_items_sort_index');
            $table->index(['dictionary_id', 'value'], 'dictionary_items_dict_value_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionary_items');
    }
};
