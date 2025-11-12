<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    use HasFactory;

    protected $table = 'wa_rules';

    protected $fillable = [
        'title',
        'icon',
        'key',
        'pid',
        'href',
        'type',
        'weight',
    ];

    protected $casts = [
        'pid' => 'integer',
        'type' => 'integer',
        'weight' => 'integer',
    ];

    /**
     * 规则类型常量
     */
    const TYPE_DIRECTORY = 0; // 目录
    const TYPE_MENU = 1;      // 菜单
    const TYPE_PERMISSION = 2; // 权限

    /**
     * 父级规则
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Rule::class, 'pid');
    }

    /**
     * 子级规则
     */
    public function children(): HasMany
    {
        return $this->hasMany(Rule::class, 'pid');
    }

    /**
     * 获取所有子孙规则
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * 获取菜单树形结构
     */
    public static function getMenuTree(): array
    {
        $rules = self::whereIn('type', [self::TYPE_DIRECTORY, self::TYPE_MENU])
                    ->orderBy('pid')
                    ->orderBy('weight')
                    ->orderBy('id')
                    ->get();
        return self::buildTree($rules->toArray());
    }

    /**
     * 获取权限树形结构
     */
    public static function getPermissionTree(): array
    {
        $rules = self::orderBy('pid')
                    ->orderBy('weight')
                    ->orderBy('id')
                    ->get();
        return self::buildTree($rules->toArray());
    }

    /**
     * 构建树形结构
     */
    private static function buildTree(array $rules, int $pid = 0): array
    {
        $tree = [];
        foreach ($rules as $rule) {
            if ($rule['pid'] == $pid) {
                $children = self::buildTree($rules, $rule['id']);
                if ($children) {
                    $rule['children'] = $children;
                }
                $tree[] = $rule;
            }
        }
        return $tree;
    }

    /**
     * 获取类型文本
     */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_DIRECTORY => '目录',
            self::TYPE_MENU => '菜单',
            self::TYPE_PERMISSION => '权限',
            default => '未知'
        };
    }
}
