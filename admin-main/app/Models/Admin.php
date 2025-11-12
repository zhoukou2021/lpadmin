<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'wa_admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'nickname',
        'password',
        'avatar',
        'email',
        'mobile',
        'login_at',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'login_at' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * 管理员角色关联
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'wa_admin_roles', 'admin_id', 'role_id');
    }

    /**
     * 获取管理员角色ID数组
     */
    public function getRoleIdsAttribute(): array
    {
        return $this->roles->pluck('id')->toArray();
    }

    /**
     * 检查是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        $roles = $this->roles;
        foreach ($roles as $role) {
            $rules = json_decode($role->rules, true) ?? [];
            if (in_array('*', $rules) || in_array($permission, $rules)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取管理员权限列表
     */
    public function getPermissions(): array
    {
        $permissions = [];
        $roles = $this->roles;
        foreach ($roles as $role) {
            $rules = json_decode($role->rules, true) ?? [];
            $permissions = array_merge($permissions, $rules);
        }
        return array_unique($permissions);
    }
}
