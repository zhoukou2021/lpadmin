<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 数据字典项模型
 *
 * @property int $id
 * @property int $dictionary_id 字典ID
 * @property string $label 显示标签
 * @property string $value 选项值
 * @property string $color 颜色标识
 * @property string $description 选项描述
 * @property int $sort 排序权重
 * @property int $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class DictionaryItem extends Model
{
    use HasFactory;

    /**
     * 数据表名称
     */
    protected $table = 'dictionary_items';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'dictionary_id',
        'label',
        'value',
        'color',
        'description',
        'sort',
        'status',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'dictionary_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 状态标签
     */
    public static $statusLabels = [
        self::STATUS_DISABLED => '禁用',
        self::STATUS_ENABLED => '启用',
    ];

    /**
     * 预设颜色选项
     */
    public static $colorOptions = [
        'blue' => '蓝色',
        'green' => '绿色',
        'orange' => '橙色',
        'red' => '红色',
        'purple' => '紫色',
        'cyan' => '青色',
        'gray' => '灰色',
        'pink' => '粉色',
    ];

    /**
     * 获取状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? '未知';
    }

    /**
     * 获取状态颜色
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status === self::STATUS_ENABLED ? 'green' : 'red';
    }

    /**
     * 获取颜色标签
     */
    public function getColorLabelAttribute(): string
    {
        return self::$colorOptions[$this->color] ?? $this->color;
    }

    /**
     * 关联字典
     */
    public function dictionary(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class);
    }

    /**
     * 查询启用状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 查询排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'desc')->orderBy('id');
    }

    /**
     * 查询指定字典的项
     */
    public function scopeOfDictionary($query, $dictionaryId)
    {
        return $query->where('dictionary_id', $dictionaryId);
    }

    /**
     * 模型事件
     */
    protected static function boot()
    {
        parent::boot();

        // 保存后清除字典缓存
        static::saved(function ($item) {
            if ($item->dictionary) {
                Dictionary::clearCache($item->dictionary->name);
            }
        });

        // 删除后清除字典缓存
        static::deleted(function ($item) {
            if ($item->dictionary) {
                Dictionary::clearCache($item->dictionary->name);
            }
        });
    }
}
