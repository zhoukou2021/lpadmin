<?php

namespace App\Http\Controllers\LPadmin;

use App\Models\LPadmin\Rule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule as ValidationRule;

class MenuController extends BaseController
{
    /**
     * 菜单列表页面
     */
    public function index(): View
    {
        return view('lpadmin.menu.index');
    }

    /**
     * 获取菜单列表数据
     */
    public function select(Request $request): JsonResponse
    {
        try {
            $query = Rule::where('type', Rule::TYPE_MENU);

            // 搜索条件
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // 树形结构排序：先按父级ID分组，再按sort排序
            // 这样可以保持树形结构的层级关系
            $query->orderBy('parent_id', 'asc')
                  ->orderBy('sort', 'desc')
                  ->orderBy('id', 'asc');

            $menus = $query->get();

            // 如果有搜索条件，需要重新组织数据以保持树形结构
            if ($request->hasAny(['title', 'status', 'type'])) {
                // 搜索时，确保包含所有相关的父级菜单
                $menuArray = $menus->toArray();
                $menuArray = $this->ensureParentMenus($menuArray);
                return $this->success($menuArray, '获取成功');
            }

            return $this->success($menus->toArray(), '获取成功');

        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 创建菜单页面
     */
    public function create(): View
    {
        $parentOptions = Rule::getParentOptions();
        return view('lpadmin.menu.create', compact('parentOptions'));
    }

    /**
     * 保存菜单
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'title' => 'required|string|max:100',
                'name' => 'required|string|max:100|unique:rules,name',
                'parent_id' => 'required|integer|min:0',
                'icon' => 'nullable|string|max:50',
                'url' => 'nullable|string|max:255',
                'type' => 'required|integer|in:0,1,2',
                'target' => 'nullable|string|max:20',
                'is_show' => 'required|integer|in:0,1',
                'status' => 'required|integer|in:0,1',
                'sort' => 'required|integer|min:0',
                'remark' => 'nullable|string',
            ];

            $messages = [
                'title.required' => '菜单标题不能为空',
                'title.max' => '菜单标题不能超过100个字符',
                'name.required' => '菜单标识不能为空',
                'name.unique' => '菜单标识已存在',
                'name.max' => '菜单标识不能超过100个字符',
                'parent_id.required' => '请选择父级菜单',
                'parent_id.integer' => '父级菜单格式错误',
                'type.required' => '请选择菜单类型',
                'type.in' => '菜单类型格式错误',
                'is_show.required' => '请选择是否显示',
                'is_show.in' => '显示状态格式错误',
                'status.required' => '请选择状态',
                'status.in' => '状态格式错误',
                'sort.required' => '排序不能为空',
                'sort.integer' => '排序必须为整数',
                'sort.min' => '排序不能小于0',
            ];

            $request->validate($rules, $messages);

            $data = $request->only([
                'parent_id', 'title', 'name', 'icon', 'url',
                'type', 'target', 'is_show', 'status', 'sort', 'remark'
            ]);

            // 如果没有传递target，默认为_self
            if (!isset($data['target'])) {
                $data['target'] = '_self';
            }

            Rule::create($data);

            return $this->success('创建成功');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('验证失败', 400, $e->errors());
        } catch (\Exception $e) {
            return $this->error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 编辑菜单页面
     */
    public function edit($id): View
    {
        $menu = Rule::findOrFail($id);
        $parentOptions = Rule::getParentOptions($id);
        return view('lpadmin.menu.edit', compact('menu', 'parentOptions'));
    }

    /**
     * 更新菜单
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $menu = Rule::findOrFail($id);

            $rules = [
                'title' => 'required|string|max:100',
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    ValidationRule::unique('rules', 'name')->ignore($id)
                ],
                'parent_id' => 'required|integer|min:0',
                'icon' => 'nullable|string|max:50',
                'url' => 'nullable|string|max:255',
                'type' => 'required|integer|in:0,1,2',
                'target' => 'nullable|string|max:20',
                'is_show' => 'required|integer|in:0,1',
                'status' => 'required|integer|in:0,1',
                'sort' => 'required|integer|min:0',
                'remark' => 'nullable|string',
            ];

            $messages = [
                'title.required' => '菜单标题不能为空',
                'title.max' => '菜单标题不能超过100个字符',
                'name.required' => '菜单标识不能为空',
                'name.unique' => '菜单标识已存在',
                'name.max' => '菜单标识不能超过100个字符',
                'parent_id.required' => '请选择父级菜单',
                'parent_id.integer' => '父级菜单格式错误',
                'type.required' => '请选择菜单类型',
                'type.in' => '菜单类型格式错误',
                'is_show.required' => '请选择是否显示',
                'is_show.in' => '显示状态格式错误',
                'status.required' => '请选择状态',
                'status.in' => '状态格式错误',
                'sort.required' => '排序不能为空',
                'sort.integer' => '排序必须为整数',
                'sort.min' => '排序不能小于0',
            ];

            $request->validate($rules, $messages);

            $data = $request->only([
                'parent_id', 'title', 'name', 'icon', 'url',
                'type', 'target', 'is_show', 'status', 'sort', 'remark'
            ]);

            // 验证父级菜单设置（避免循环引用）
            if ($data['parent_id'] != 0 && !$menu->canSetAsParent($data['parent_id'])) {
                return $this->error('不能将自己或子菜单设置为父菜单');
            }

            // 如果没有传递target，默认为_self
            if (!isset($data['target'])) {
                $data['target'] = '_self';
            }

            $menu->update($data);

            return $this->success('更新成功');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('验证失败', 400, $e->errors());
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 快速更新排序
     */
    public function updateSort(Request $request, $id): JsonResponse
    {
        try {
            $menu = Rule::findOrFail($id);

            $request->validate([
                'sort' => 'required|integer|min:0|max:9999'
            ]);

            $menu->update([
                'sort' => $request->sort
            ]);

            return $this->success('排序更新成功');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('验证失败', 400, $e->errors());
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除菜单
     */
    public function destroy($id): JsonResponse
    {
        try {
            $menu = Rule::findOrFail($id);

            // 检查是否有子菜单
            if ($menu->hasChildren()) {
                return $this->error('该菜单下还有子菜单，无法删除');
            }

            $menu->delete();

            return $this->success('删除成功');

        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量删除菜单
     */
    public function batchDestroy(Request $request): JsonResponse
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return $this->error('请选择要删除的菜单');
            }

            // 检查是否有子菜单
            foreach ($ids as $id) {
                $menu = Rule::find($id);
                if ($menu && $menu->hasChildren()) {
                    return $this->error("菜单「{$menu->title}」下还有子菜单，无法删除");
                }
            }

            Rule::whereIn('id', $ids)->delete();

            return $this->success('批量删除成功');

        } catch (\Exception $e) {
            return $this->error('批量删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取菜单树形数据（用于权限规则选择）
     * 根据当前用户权限过滤菜单
     */
    public function tree(): JsonResponse
    {
        try {
            $admin = auth('lpadmin')->user();

            if (!$admin) {
                return $this->error('未登录', 401);
            }

            // 使用权限服务获取用户可见的菜单
            $permissionService = app(\App\Services\LPadmin\PermissionService::class);

            if ($permissionService->isSuperAdmin($admin)) {
                // 超级管理员显示所有权限规则
                $menus = Rule::where('status', Rule::STATUS_ENABLED)
                            ->orderBy('sort', 'desc')
                            ->orderBy('id')
                            ->get(['id', 'parent_id', 'name', 'title', 'type', 'icon'])
                            ->toArray();
            } else {
                // 普通用户使用权限服务获取授权权限
                $permissions = $permissionService->getAdminPermissions($admin);

                // 获取所有启用的权限规则
                $allMenus = Rule::where('status', Rule::STATUS_ENABLED)
                            ->orderBy('sort', 'desc')
                            ->orderBy('id')
                            ->get(['id', 'parent_id', 'name', 'title', 'type', 'icon']);

                // 使用权限服务过滤权限
                $menus = [];
                foreach ($allMenus as $menu) {
                    if ($this->checkMenuPermission($menu, $permissions)) {
                        $menus[] = $menu->toArray();
                    }
                }
            }

            $tree = $this->buildTree($menus);

            return $this->success($tree, '获取成功');

        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 构建树形结构
     */
    private function buildTree(array $menus, int $parentId = 0): array
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = $this->buildTree($menus, $menu['id']);
                if (!empty($children)) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }

        return $tree;
    }

    /**
     * 确保搜索结果包含所有相关的父级菜单
     */
    private function ensureParentMenus(array $menus): array
    {
        $menuIds = array_column($menus, 'id');
        $parentIds = array_unique(array_column($menus, 'parent_id'));

        // 找出缺失的父级菜单
        $missingParentIds = array_diff($parentIds, $menuIds);
        $missingParentIds = array_filter($missingParentIds, function($id) {
            return $id > 0; // 排除顶级菜单的parent_id=0
        });

        if (!empty($missingParentIds)) {
            // 获取缺失的父级菜单
            $missingParents = Rule::whereIn('id', $missingParentIds)->where('type', Rule::TYPE_MENU)->get()->toArray();

            // 合并菜单数据
            $menus = array_merge($menus, $missingParents);

            // 重新排序以保持树形结构
            usort($menus, function($a, $b) {
                if ($a['parent_id'] == $b['parent_id']) {
                    if ($a['sort'] == $b['sort']) {
                        return $a['id'] - $b['id'];
                    }
                    return $b['sort'] - $a['sort']; // sort 降序
                }
                return $a['parent_id'] - $b['parent_id']; // parent_id 升序
            });
        }

        return $menus;
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
