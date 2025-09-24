<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Models\LPadmin\Rule;

class RuleController extends BaseController
{
    /**
     * 权限规则列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax() || $request->has('format')) {
            $query = Rule::query();

            // 根据格式返回不同数据
            $format = $request->get('format');

            if ($format === 'tree') {
                // 树形选择器格式 - 只获取启用的菜单权限
                $rules = $query->where('status', Rule::STATUS_ENABLED)
                              ->where('type', 'menu')
                              ->orderBy('sort')
                              ->orderBy('id')
                              ->get(['id', 'parent_id', 'title']);
                $treeData = $this->buildTree($rules->toArray());
                return $this->success($treeData);
            }

            // 默认表格格式 - 获取所有权限
            // 搜索条件
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 获取树形数据
            $rules = $query->orderBy('sort')->orderBy('id')->get();

            // 由于已经使用了全局时间格式化中间件，这里不需要手动格式化时间
            // 直接返回平铺数据，让treetable自己构建树形结构
            return $this->success($rules->toArray());
        }

        return view('lpadmin.rule.index');
    }

    /**
     * 获取菜单树形数据（用于PearAdminLayui）
     * 根据当前用户权限过滤菜单
     */
    public function tree(): JsonResponse
    {
        try {
            $admin = auth('lpadmin')->user();

            if (!$admin) {
                return response()->json([
                    'code' => 401,
                    'message' => '未登录',
                    'data' => []
                ]);
            }

            // 使用权限服务获取用户可见的菜单
            $permissionService = app(\App\Services\LPadmin\PermissionService::class);

            if ($permissionService->isSuperAdmin($admin)) {
                // 超级管理员显示所有菜单类型的权限
                $menus = Rule::where('status', Rule::STATUS_ENABLED)
                            ->where('type', Rule::TYPE_MENU) // 只显示菜单类型
                            ->where('is_show', Rule::SHOW_VISIBLE) // 只显示可见菜单
                            ->where('id','>',1)
                            ->orderBy('sort', 'desc')
                            ->orderBy('id')
                            ->get();
            } else {
                // 普通用户使用权限服务获取授权权限
                $permissions = $permissionService->getAdminPermissions($admin);

                // 获取所有启用的菜单类型权限规则
                $allMenus = Rule::where('status', Rule::STATUS_ENABLED)
                            ->where('type', Rule::TYPE_MENU) // 只显示菜单类型
                            ->where('is_show', Rule::SHOW_VISIBLE) // 只显示可见菜单
                            ->where('id','>',1)
                            ->orderBy('sort', 'desc')
                            ->orderBy('id')
                            ->get();

                // 使用权限服务过滤权限
                $menus = collect();
                foreach ($allMenus as $menu) {
                    if ($this->checkMenuPermission($menu, $permissions)) {
                        $menus->push($menu);
                    }
                }
            }

            // 构建PearAdminLayui格式的菜单树
            $menuTree = $this->buildMenuTree($menus->toArray());

            return response()->json([
                'code' => 200,
                'message' => 'success',
                'data' => $menuTree
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '获取菜单失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 获取权限规则树形数据（用于权限分配）
     */
    public function permissionTree(): JsonResponse
    {
        try {
            // 获取启用的权限规则数据
            $rules = Rule::where('status', Rule::STATUS_ENABLED)
                        ->orderBy('sort', 'desc')
                        ->orderBy('id')
                        ->get();

            // 构建dtree格式的权限树
            $ruleTree = $this->buildRuleTree($rules->toArray());

            return response()->json([
                'code' => 0,
                'msg' => 'success',
                'data' => $ruleTree
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取权限规则失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 创建权限规则页面
     */
    public function create(): View
    {
        $parentRules = Rule::where('type', 'menu')
                          ->where('status', Rule::STATUS_ENABLED)
                          ->orderBy('sort')
                          ->get();

        return view('lpadmin.rule.create', compact('parentRules'));
    }

    /**
     * 保存权限规则
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:rules,name',
            'title' => 'required|string|max:100',
            'type' => 'required|in:menu,button,api',
            'parent_id' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string',
            'component' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'remark' => 'nullable|string',
        ], [
            'name.required' => '权限名称不能为空',
            'name.unique' => '权限名称已存在',
            'title.required' => '权限标题不能为空',
            'type.required' => '权限类型不能为空',
            'type.in' => '权限类型无效',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = $request->only([
                'name', 'title', 'type', 'parent_id', 'icon',
                'route_name', 'url', 'component', 'sort', 'remark'
            ]);

            $data['parent_id'] = $data['parent_id'] ?: 0;
            $data['sort'] = $data['sort'] ?: 0;
            $data['status'] = Rule::STATUS_ENABLED;

            // 验证父级权限是否存在（当parent_id不为0时）
            if ($data['parent_id'] > 0) {
                $parentRule = Rule::find($data['parent_id']);
                if (!$parentRule) {
                    return $this->error('指定的父级权限不存在');
                }
            }

            $rule = Rule::create($data);

            $this->log('create', '创建权限规则', ['rule_id' => $rule->id]);

            return $this->success($rule, '创建成功');
        } catch (\Exception $e) {
            return $this->error('创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示权限规则详情
     */
    public function show(Rule $rule): JsonResponse
    {
        return $this->success($rule);
    }

    /**
     * 编辑权限规则页面
     */
    public function edit(Rule $rule): View
    {
        $parentRules = Rule::where('type', 'menu')
                          ->where('status', Rule::STATUS_ENABLED)
                          ->where('id', '!=', $rule->id)
                          ->orderBy('sort')
                          ->get();

        return view('lpadmin.rule.edit', compact('rule', 'parentRules'));
    }

    /**
     * 更新权限规则
     */
    public function update(Request $request, Rule $rule): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:rules,name,' . $rule->id,
            'title' => 'required|string|max:100',
            'type' => 'required|in:menu,button,api',
            'parent_id' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:50',
            'route_name' => 'nullable|string|max:100',
            'url' => 'nullable|string',
            'component' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'remark' => 'nullable|string',
        ], [
            'name.required' => '权限名称不能为空',
            'name.unique' => '权限名称已存在',
            'title.required' => '权限标题不能为空',
            'type.required' => '权限类型不能为空',
            'type.in' => '权限类型无效',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = $request->only([
                'name', 'title', 'type', 'parent_id', 'icon',
                'route_name', 'url', 'component', 'sort', 'remark'
            ]);

            $data['parent_id'] = $data['parent_id'] ?: 0;
            $data['sort'] = $data['sort'] ?: 0;

            // 验证父级权限是否存在（当parent_id不为0时）
            if ($data['parent_id'] > 0) {
                $parentRule = Rule::find($data['parent_id']);
                if (!$parentRule) {
                    return $this->error('指定的父级权限不存在');
                }

                // 防止将自己设置为父级
                if ($data['parent_id'] == $rule->id) {
                    return $this->error('不能将自己设置为父级权限');
                }
            }

            $rule->update($data);

            $this->log('update', '更新权限规则', ['rule_id' => $rule->id]);

            return $this->success($rule, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除权限规则
     */
    public function destroy(Rule $rule): JsonResponse
    {
        try {
            // 检查是否有子权限
            if ($rule->children()->count() > 0) {
                return $this->error('该权限下还有子权限，无法删除');
            }

            // 检查是否被角色使用
            if ($rule->roles()->count() > 0) {
                return $this->error('该权限正在被角色使用，无法删除');
            }

            $rule->delete();

            $this->log('delete', '删除权限规则', ['rule_id' => $rule->id]);

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换权限规则状态
     */
    public function toggleStatus(Rule $rule): JsonResponse
    {
        try {
            $newStatus = $rule->status === Rule::STATUS_ENABLED
                ? Rule::STATUS_DISABLED
                : Rule::STATUS_ENABLED;

            $rule->update(['status' => $newStatus]);

            $this->log('update', '切换权限规则状态', ['rule_id' => $rule->id, 'status' => $newStatus]);

            return $this->success(null, '操作成功');
        } catch (\Exception $e) {
            return $this->error('操作失败: ' . $e->getMessage());
        }
    }

    /**
     * 构建树形结构
     */
    private function buildTree(array $data, int $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($data, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * 构建dtree格式的权限规则树
     */
    private function buildRuleTree(array $data, int $parentId = 0): array
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $node = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'parentId' => $item['parent_id'],
                    'checkArr' => '0',
                    'spread' => false
                ];

                // 递归查找子节点
                $children = $this->buildRuleTree($data, $item['id']);
                if (!empty($children)) {
                    $node['children'] = $children;
                    $node['spread'] = true; // 有子节点时默认展开
                }

                $tree[] = $node;
            }
        }
        return $tree;
    }

    /**
     * 构建PearAdminLayui格式的菜单树
     */
    private function buildMenuTree(array $data, int $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $menuItem = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'icon' => 'layui-icon ' . ($item['icon'] ?: 'layui-icon-app'),
                    'href' => $item['url'] ?: '',
                    'spread' => false,
                    'type' => 1, // 默认为菜单类型
                ];

                $children = $this->buildMenuTree($data, $item['id']);
                if (!empty($children)) {
                    $menuItem['children'] = $children;
                    $menuItem['spread'] = true;
                    $menuItem['type'] = 0; // 有子菜单的为目录类型
                }

                $tree[] = $menuItem;
            }
        }

        return $tree;
    }

    /**
     * 检查菜单权限
     *
     * @param \App\Models\LPadmin\Rule $menu
     * @param array $permissions
     * @return bool
     */
    private function checkMenuPermission($menu, array $permissions): bool
    {
        // 1. 检查是否被明确拒绝（deny.菜单名）
        if (in_array('deny.' . $menu->name, $permissions)) {
            return false;
        }

        // 2. 直接权限匹配 - 必须有明确的权限
        if (in_array($menu->name, $permissions)) {
            return true;
        }

        // 3. 子菜单不继承父菜单权限，必须有明确的权限才能显示
        // 这确保了精确的权限控制：只显示明确授权的菜单

        return false;
    }

    /**
     * 过滤菜单权限（排除功能权限）
     *
     * @param array $permissions
     * @return array
     */
    private function filterMenuPermissions(array $permissions): array
    {
        $menuPermissions = [];

        foreach ($permissions as $permission) {
            // 只保留不包含点号的权限（主权限）作为菜单权限
            // 例如：'user' 是菜单权限，'user.view' 是功能权限
            if (strpos($permission, '.') === false) {
                $menuPermissions[] = $permission;
            }
        }

        return $menuPermissions;
    }
}
