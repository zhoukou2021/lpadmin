<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * 系统配置模型
 *
 * @property int $id
 * @property string $group 配置分组
 * @property string $name 配置名称
 * @property string $title 配置标题
 * @property string $value 配置值
 * @property string $type 配置类型
 * @property string $options 选项配置
 * @property string $description 描述
 * @property int $sort 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Option extends Model
{
    use HasFactory;

    /**
     * 数据表名称
     */
    protected $table = 'options';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'group',
        'name',
        'title',
        'value',
        'type',
        'options',
        'description',
        'sort',
        'is_i18n',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'sort' => 'integer',
        'is_i18n' => 'boolean',
    ];

    /**
     * 配置类型常量
     */
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_NUMBER = 'number';
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_SWITCH = 'switch';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_COLOR = 'color';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_RICHTEXT = 'richtext';

    /**
     * 配置类型标签
     */
    public static $typeLabels = [
        self::TYPE_TEXT => '文本框',
        self::TYPE_TEXTAREA => '文本域',
        self::TYPE_NUMBER => '数字',
        self::TYPE_SELECT => '下拉选择',
        self::TYPE_RADIO => '单选框',
        self::TYPE_CHECKBOX => '复选框',
        self::TYPE_SWITCH => '开关',
        self::TYPE_IMAGE => '图片',
        self::TYPE_FILE => '文件',
        self::TYPE_COLOR => '颜色',
        self::TYPE_DATE => '日期',
        self::TYPE_DATETIME => '日期时间',
        self::TYPE_RICHTEXT => '富文本',
    ];

    /**
     * 获取配置类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? '未知';
    }

    /**
     * 获取选项配置（JSON解码）
     */
    public function getOptionsArrayAttribute(): array
    {
        return json_decode($this->options, true) ?: [];
    }

    /**
     * 获取格式化的值
     */
    public function getFormattedValueAttribute()
    {
        switch ($this->type) {
            case self::TYPE_SWITCH:
            case self::TYPE_CHECKBOX:
                return (bool) $this->value;
            case self::TYPE_NUMBER:
                return (int) $this->value;
            case self::TYPE_SELECT:
            case self::TYPE_RADIO:
                $options = $this->options_array;
                return $options[$this->value] ?? $this->value;
            default:
                return $this->value;
        }
    }

    /**
     * 查询指定分组的配置
     */
    public function scopeOfGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * 查询排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 获取配置值
     */
    public static function getValue(string $name, $default = null)
    {
        $cacheKey = 'lpadmin_option_' . $name;

        return Cache::remember($cacheKey, 3600, function () use ($name, $default) {
            $option = self::where('name', $name)->first();
            return $option ? $option->value : $default;
        });
    }

    /**
     * 设置配置值
     */
    public static function setValue(string $name, $value): bool
    {
        $option = self::where('name', $name)->first();

        if ($option) {
            $result = $option->update(['value' => $value]);
        } else {
            $result = self::create([
                'name' => $name,
                'value' => $value,
                'group' => 'system',
                'title' => $name,
                'type' => self::TYPE_TEXT,
            ]);
        }

        // 清除缓存
        Cache::forget('lpadmin_option_' . $name);

        return (bool) $result;
    }

    /**
     * 批量设置配置值
     */
    public static function setValues(array $values): bool
    {
        $success = true;

        foreach ($values as $name => $value) {
            if (!self::setValue($name, $value)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 获取分组配置
     */
    public static function getGroupValues(string $group): array
    {
        $cacheKey = 'lpadmin_options_group_' . $group;

        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return self::where('group', $group)
                      ->pluck('value', 'name')
                      ->toArray();
        });
    }

    /**
     * 清除配置缓存
     */
    public static function clearCache(string $name = null): void
    {
        if ($name) {
            Cache::forget('lpadmin_option_' . $name);
        } else {
            // 清除所有配置缓存
            $options = self::pluck('name');
            foreach ($options as $optionName) {
                Cache::forget('lpadmin_option_' . $optionName);
            }

            // 清除分组缓存
            $groups = self::distinct('group')->pluck('group');
            foreach ($groups as $group) {
                Cache::forget('lpadmin_options_group_' . $group);
            }
        }
    }
}
