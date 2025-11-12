<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TimeFormattable;

/**
 * 权限规则模型
 *
 * @property int $id
 * @property int $parent_id 父级ID
 * @property string $name 权限名称
 * @property string $title 权限标题
 * @property string $type 类型 menu:菜单 button:按钮 api:接口
 * @property string $icon 图标
 * @property string $route_name 路由名称
 * @property string $url URL地址
 * @property string $component 组件路径
 * @property int $status 状态 1:启用 0:禁用
 * @property int $sort 排序
 * @property string $remark 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at 删除时间
 */
class Rule extends Model
{
    use HasFactory, SoftDeletes, TimeFormattable;

    /**
     * 数据表名称
     */
    protected $table = 'rules';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'type',
        'icon',
        'route_name',
        'url',
        'component',
        'target',
        'is_show',
        'status',
        'sort',
        'remark',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'parent_id' => 'integer',
        'status' => 'integer',
        'is_show' => 'boolean',
        'sort' => 'integer',
    ];

    /**
     * 类型常量
     */
    const TYPE_MENU = 'menu';
    const TYPE_BUTTON = 'button';
    const TYPE_API = 'api';
    const TYPE_DIRECTORY = 'directory';
    const TYPE_DENY = 'deny';

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 显示常量
     */
    const SHOW_HIDDEN = 0;
    const SHOW_VISIBLE = 1;

    /**
     * 类型标签
     */
    public static $typeLabels = [
        self::TYPE_MENU => '菜单',
        self::TYPE_BUTTON => '按钮',
        self::TYPE_API => '接口',
        self::TYPE_DIRECTORY => '目录',
        self::TYPE_DENY => '拒绝',
    ];

    /**
     * 状态标签
     */
    public static $statusLabels = [
        self::STATUS_DISABLED => '禁用',
        self::STATUS_ENABLED => '启用',
    ];

    /**
     * 获取类型标签
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
     * 检查是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 检查是否为菜单
     */
    public function isMenu(): bool
    {
        return $this->type === self::TYPE_MENU;
    }

    /**
     * 检查是否为按钮
     */
    public function isButton(): bool
    {
        return $this->type === self::TYPE_BUTTON;
    }

    /**
     * 检查是否为接口
     */
    public function isApi(): bool
    {
        return $this->type === self::TYPE_API;
    }

    /**
     * 检查是否显示
     */
    public function isVisible(): bool
    {
        return $this->is_show === self::SHOW_VISIBLE;
    }

    /**
     * 启用规则
     */
    public function enable(): bool
    {
        return $this->update(['status' => self::STATUS_ENABLED]);
    }

    /**
     * 禁用规则
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
        return $this->belongsToMany(Role::class, 'lp_role_rules', 'rule_id', 'role_id')
                    ->withTimestamps();
    }

    /**
     * 关联菜单（已废弃，现在Rule模型本身就包含菜单功能）
     * @deprecated 菜单功能已合并到Rule模型中
     */
    public function menu(): BelongsTo
    {
        // 返回自身关联，因为Rule模型现在包含菜单功能
        return $this->belongsTo(self::class, 'id', 'id');
    }

    /**
     * 关联父级规则
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 关联子级规则
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * 获取所有子级规则（递归）
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * 获取层级路径
     */
    public function getPathAttribute(): string
    {
        $path = [$this->title];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->title);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * 获取层级深度
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * 查询启用的规则
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 查询指定类型的规则
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 查询菜单规则
     */
    public function scopeMenus($query)
    {
        return $query->where('type', self::TYPE_MENU);
    }

    /**
     * 查询顶级规则
     */
    public function scopeTopLevel($query)
    {
        return $query->where('parent_id', 0);
    }

    /**
     * 查询排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }

    /**
     * 查询显示的规则
     */
    public function scopeVisible($query)
    {
        return $query->where('is_show', self::SHOW_VISIBLE);
    }

    /**
     * 获取菜单树（只返回菜单类型且显示的记录）
     */
    public static function getMenuTree($parentId = 0, $userPermissions = null)
    {
        $query = static::where('parent_id', $parentId)
            ->where('type', static::TYPE_MENU)
            ->where('is_show', static::SHOW_VISIBLE)
            ->where('status', static::STATUS_ENABLED)
            ->orderBy('sort', 'desc');

        // 如果提供了用户权限，则过滤
        if ($userPermissions !== null) {
            $query->whereIn('name', $userPermissions);
        }

        return $query->with(['children' => function ($query) use ($userPermissions) {
                $query->where('type', static::TYPE_MENU)
                    ->where('is_show', static::SHOW_VISIBLE)
                    ->where('status', static::STATUS_ENABLED)
                    ->orderBy('sort', 'desc');
                if ($userPermissions !== null) {
                    $query->whereIn('name', $userPermissions);
                }
            }])
            ->get();
    }

    /**
     * 获取权限树（包含所有类型）
     */
    public static function getPermissionTree($parentId = 0)
    {
        return static::where('parent_id', $parentId)
            ->where('status', static::STATUS_ENABLED)
            ->orderBy('sort', 'desc')
            ->with(['children' => function ($query) {
                $query->where('status', static::STATUS_ENABLED)
                    ->orderBy('sort', 'desc');
            }])
            ->get();
    }

    /**
     * 获取所有权限的扁平数组
     */
    public static function getAllPermissions()
    {
        return static::where('status', static::STATUS_ENABLED)
            ->pluck('name')
            ->toArray();
    }

    /**
     * 检查权限是否存在
     */
    public static function permissionExists($permission)
    {
        return static::where('name', $permission)
            ->where('status', static::STATUS_ENABLED)
            ->exists();
    }

    /**
     * 获取所有父级选项（用于下拉选择）
     */
    public static function getParentOptions($excludeId = null): array
    {
        $query = static::where('status', static::STATUS_ENABLED)
                    ->where('type', static::TYPE_MENU)
                    ->orderBy('sort', 'desc')
                    ->orderBy('id');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $rules = $query->get()->toArray();

        // 构建树形结构
        $tree = static::buildRuleTree($rules);

        // 扁平化为选项数组，添加层级指示符
        $options = [['id' => 0, 'title' => '顶级菜单']];
        static::flattenRuleOptions($tree, $options);

        return $options;
    }

    /**
     * 构建规则树
     */
    private static function buildRuleTree(array $data, int $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = static::buildRuleTree($data, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * 扁平化规则选项
     */
    private static function flattenRuleOptions(array $tree, array &$options, string $prefix = ''): void
    {
        foreach ($tree as $item) {
            $options[] = [
                'id' => $item['id'],
                'title' => $prefix . $item['title']
            ];

            if (!empty($item['children'])) {
                static::flattenRuleOptions($item['children'], $options, $prefix . '├─ ');
            }
        }
    }

    /**
     * 检查是否有子级
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * 检查是否可以设置为父级（避免循环引用）
     */
    public function canSetAsParent(int $parentId): bool
    {
        // 不能将自己设置为父级
        if ($parentId == $this->id) {
            return false;
        }

        // 不能将自己的子级或子子级设置为父级
        $childIds = $this->getAllChildIds();
        return !in_array($parentId, $childIds);
    }

    /**
     * 获取所有子级ID（递归）
     */
    public function getAllChildIds(): array
    {
        $childIds = [];
        $children = $this->children;

        foreach ($children as $child) {
            $childIds[] = $child->id;
            $childIds = array_merge($childIds, $child->getAllChildIds());
        }

        return $childIds;
    }

    /**
     * 获取显示状态标签
     */
    public function getShowLabelAttribute(): string
    {
        return $this->is_show == static::SHOW_VISIBLE ? '显示' : '隐藏';
    }

    /**
     * 获取树形结构（兼容Menu模型的getTree方法）
     */
    public static function getTree($parentId = 0): array
    {
        $rules = static::where('parent_id', $parentId)
                    ->where('status', static::STATUS_ENABLED)
                    ->where('type', static::TYPE_MENU)
                    ->orderBy('sort', 'desc')
                    ->orderBy('id')
                    ->get();

        $tree = [];
        foreach ($rules as $rule) {
            $item = $rule->toArray();
            $children = static::getTree($rule->id);
            if (!empty($children)) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }

        return $tree;
    }
}
