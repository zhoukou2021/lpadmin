<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    use HasFactory;

    protected $table = 'wa_roles';

    protected $fillable = [
        'name',
        'rules',
        'pid',
    ];

    protected $casts = [
        'pid' => 'integer',
    ];

    /**
     * 角色管理员关联
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'wa_admin_roles', 'role_id', 'admin_id');
    }

    /**
     * 父级角色
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'pid');
    }

    /**
     * 子级角色
     */
    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'pid');
    }

    /**
     * 获取所有子孙角色
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * 获取权限规则数组
     */
    public function getRulesArrayAttribute(): array
    {
        return json_decode($this->rules, true) ?? [];
    }

    /**
     * 设置权限规则
     */
    public function setRulesArrayAttribute(array $rules): void
    {
        $this->attributes['rules'] = json_encode($rules);
    }

    /**
     * 检查是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        $rules = $this->rules_array;
        return in_array('*', $rules) || in_array($permission, $rules);
    }

    /**
     * 获取角色树形结构
     */
    public static function getTree(): array
    {
        $roles = self::orderBy('pid')->orderBy('id')->get();
        return self::buildTree($roles->toArray());
    }

    /**
     * 构建树形结构
     */
    private static function buildTree(array $roles, int $pid = 0): array
    {
        $tree = [];
        foreach ($roles as $role) {
            if ($role['pid'] == $pid) {
                $children = self::buildTree($roles, $role['id']);
                if ($children) {
                    $role['children'] = $children;
                }
                $tree[] = $role;
            }
        }
        return $tree;
    }
}
