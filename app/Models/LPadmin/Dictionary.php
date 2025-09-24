<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * 数据字典模型
 *
 * @property int $id
 * @property string $name 字典名称（唯一标识）
 * @property string $title 字典标题
 * @property string $type 字典类型
 * @property string $description 字典描述
 * @property int $sort 排序权重
 * @property int $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Dictionary extends Model
{
    use HasFactory;

    /**
     * 数据表名称
     */
    protected $table = 'dictionaries';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'title',
        'type',
        'description',
        'sort',
        'status',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'sort' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 字典类型常量
     */
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 字典类型标签
     */
    public static $typeLabels = [
        self::TYPE_SELECT => '下拉选择',
        self::TYPE_RADIO => '单选框',
        self::TYPE_CHECKBOX => '复选框',
    ];

    /**
     * 状态标签
     */
    public static $statusLabels = [
        self::STATUS_DISABLED => '禁用',
        self::STATUS_ENABLED => '启用',
    ];

    /**
     * 获取字典类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? '未知';
    }

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
     * 关联字典项
     */
    public function items(): HasMany
    {
        return $this->hasMany(DictionaryItem::class)->orderBy('sort')->orderBy('id');
    }

    /**
     * 关联启用的字典项
     */
    public function enabledItems(): HasMany
    {
        return $this->items()->where('status', DictionaryItem::STATUS_ENABLED);
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
     * 获取字典数据（带缓存）
     */
    public static function getDictData(string $name): array
    {
        $cacheKey = 'lpadmin_dict_' . $name;

        return Cache::remember($cacheKey, 3600, function () use ($name) {
            $dictionary = self::with('enabledItems')
                             ->where('name', $name)
                             ->where('status', self::STATUS_ENABLED)
                             ->first();

            if (!$dictionary) {
                return [];
            }

            return $dictionary->enabledItems->map(function ($item) {
                return [
                    'label' => $item->label,
                    'value' => $item->value,
                    'color' => $item->color,
                    'description' => $item->description,
                ];
            })->toArray();
        });
    }

    /**
     * 清除字典缓存
     */
    public static function clearCache(string $name = null): void
    {
        if ($name) {
            Cache::forget('lpadmin_dict_' . $name);
        } else {
            // 清除所有字典缓存
            $dictionaries = self::pluck('name');
            foreach ($dictionaries as $dictName) {
                Cache::forget('lpadmin_dict_' . $dictName);
            }
        }
    }

    /**
     * 模型事件
     */
    protected static function boot()
    {
        parent::boot();

        // 保存后清除缓存
        static::saved(function ($dictionary) {
            self::clearCache($dictionary->name);
        });

        // 删除后清除缓存
        static::deleted(function ($dictionary) {
            self::clearCache($dictionary->name);
        });
    }
}
