<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 角色模型
 *
 * @property int $id
 * @property string $name 角色名称
 * @property string $display_name 显示名称
 * @property string $description 描述
 * @property int $status 状态 1:启用 0:禁用
 * @property int $sort 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 数据表名称
     */
    protected $table = 'roles';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'status',
        'sort',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'status' => 'integer',
        'sort' => 'integer',
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
     * 获取状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? '未知';
    }

    /**
     * 检查是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 启用角色
     */
    public function enable(): bool
    {
        return $this->update(['status' => self::STATUS_ENABLED]);
    }

    /**
     * 禁用角色
     */
    public function disable(): bool
    {
        return $this->update(['status' => self::STATUS_DISABLED]);
    }

    /**
     * 关联管理员
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_roles', 'role_id', 'admin_id')
                    ->withTimestamps();
    }

    /**
     * 关联权限规则
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, 'role_rules', 'role_id', 'rule_id')
                    ->withTimestamps();
    }

    /**
     * 检查是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        return $this->rules()->where('name', $permission)->exists();
    }

    /**
     * 分配权限
     */
    public function givePermission($rule): void
    {
        if (is_string($rule)) {
            $rule = Rule::where('name', $rule)->first();
        }

        if ($rule && !$this->rules()->where('rule_id', $rule->id)->exists()) {
            $this->rules()->attach($rule->id);
        }
    }

    /**
     * 移除权限
     */
    public function revokePermission($rule): void
    {
        if (is_string($rule)) {
            $rule = Rule::where('name', $rule)->first();
        }

        if ($rule) {
            $this->rules()->detach($rule->id);
        }
    }

    /**
     * 同步权限
     */
    public function syncPermissions(array $ruleIds): void
    {
        $this->rules()->sync($ruleIds);
    }

    /**
     * 获取权限数量
     */
    public function getPermissionCountAttribute(): int
    {
        return $this->rules()->count();
    }

    /**
     * 获取管理员数量
     */
    public function getAdminCountAttribute(): int
    {
        return $this->admins()->count();
    }

    /**
     * 查询启用的角色
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
        return $query->orderBy('sort')->orderBy('id');
    }
}
