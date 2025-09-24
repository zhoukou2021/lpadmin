<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 组件模型
 *
 * @property int $id
 * @property string $name 组件名称
 * @property string $title 组件标题
 * @property string $description 组件描述
 * @property string $version 组件版本
 * @property string $author 组件作者
 * @property array $config 组件配置
 * @property int $status 状态
 * @property string $installed_at 安装时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Component extends Model
{
    use HasFactory;

    /**
     * 数据表名称
     */
    protected $table = 'components';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'title',
        'description',
        'version',
        'author',
        'config',
        'status',
        'installed_at',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'config' => 'array',
        'status' => 'integer',
        'installed_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_UNINSTALLED = 0;
    const STATUS_INSTALLED = 1;

    /**
     * 状态标签
     */
    public static $statusLabels = [
        self::STATUS_UNINSTALLED => '未安装',
        self::STATUS_INSTALLED => '已安装',
    ];

    /**
     * 获取状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? '未知';
    }

    /**
     * 检查是否已安装
     */
    public function isInstalled(): bool
    {
        return $this->status === self::STATUS_INSTALLED;
    }

    /**
     * 安装组件
     */
    public function install(): bool
    {
        return $this->update([
            'status' => self::STATUS_INSTALLED,
            'installed_at' => now(),
        ]);
    }

    /**
     * 卸载组件
     */
    public function uninstall(): bool
    {
        return $this->update([
            'status' => self::STATUS_UNINSTALLED,
            'installed_at' => null,
        ]);
    }

    /**
     * 作用域：已安装的组件
     */
    public function scopeInstalled($query)
    {
        return $query->where('status', self::STATUS_INSTALLED);
    }

    /**
     * 作用域：未安装的组件
     */
    public function scopeUninstalled($query)
    {
        return $query->where('status', self::STATUS_UNINSTALLED);
    }

    /**
     * 根据名称查找组件
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * 创建或更新组件
     */
    public static function createOrUpdate(array $data): self
    {
        return static::updateOrCreate(
            ['name' => $data['name']],
            $data
        );
    }
}
