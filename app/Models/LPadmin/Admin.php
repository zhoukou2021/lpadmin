<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\TimeFormattable;

/**
 * 管理员模型
 *
 * @property int $id
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $nickname 昵称
 * @property string $email 邮箱
 * @property string $phone 手机号
 * @property string $avatar 头像
 * @property int $status 状态 1:启用 0:禁用
 * @property string $last_login_at 最后登录时间
 * @property string $last_login_ip 最后登录IP
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class Admin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, TimeFormattable;

    /**
     * 数据表名称
     */
    protected $table = 'admins';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\LPadmin\AdminFactory::new();
    }

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'username',
        'password',
        'nickname',
        'email',
        'phone',
        'avatar',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * 隐藏的属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'status' => 'integer',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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
     * 获取头像URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        return asset('static/admin/images/avatar.png');
    }

    /**
     * 格式化最后登录时间
     */
    public function getLastLoginAtFormattedAttribute(): string
    {
        return $this->last_login_at ? $this->last_login_at->format('Y-m-d H:i:s') : '从未登录';
    }

    /**
     * 检查是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 启用管理员
     */
    public function enable(): bool
    {
        return $this->update(['status' => self::STATUS_ENABLED]);
    }

    /**
     * 禁用管理员
     */
    public function disable(): bool
    {
        return $this->update(['status' => self::STATUS_DISABLED]);
    }

    /**
     * 关联角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_roles', 'admin_id', 'role_id')
                    ->withTimestamps();
    }

    /**
     * 关联操作日志
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }

    /**
     * 检查是否有指定角色
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * 检查是否有任意角色
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * 分配角色
     */
    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role && !$this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * 移除角色
     */
    public function removeRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    /**
     * 同步角色
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }
}
