<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use App\Models\LPadmin\Role;
use App\Models\LPadmin\Rule;

/**
 * 角色控制器
 */
class RoleController extends BaseController
{
    /**
     * 显示角色列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson() || $request->has('format')) {
            // 根据格式返回不同数据
            $format = $request->get('format');

            if ($format === 'select' || $format === 'tree') {
                // 选择器格式只获取启用的角色
                $query = Role::where('status', 1);
                $roles = $query->ordered()->get(['id', 'name', 'display_name']);
                $data = $roles->map(function($role) {
                    return [
                        'value' => $role->id,
                        'name' => $role->display_name,
                        'id' => $role->id,
                        'display_name' => $role->display_name
                    ];
                });

                return response()->json([
                    'code' => 0,
                    'message' => 'success',
                    'data' => $data
                ]);
            }

            // 默认分页格式 - 获取所有角色
            $query = Role::query();

            // 搜索条件
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('display_name')) {
                $query->where('display_name', 'like', '%' . $request->display_name . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->withCount(['admins', 'rules']);
            $roles = $query->ordered()->paginate($request->get('limit', 15));

            // 转换字段名以匹配前端期望
            $roles->getCollection()->transform(function ($role) {
                $role->admin_count = $role->admins_count;
                $role->permission_count = $role->rules_count;
                return $role;
            });

            return $this->paginate($roles);
        }

        return $this->view('role.index');
    }

    /**
     * 显示创建角色表单
     */
    public function create(): View
    {
        return $this->view('role.create');
    }

    /**
     * 存储新角色
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            'sort' => 'nullable|integer|min:0',
        ], [
            'name.required' => '角色名称不能为空',
            'name.unique' => '角色名称已存在',
            'display_name.required' => '显示名称不能为空',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'status' => $request->status,
                'sort' => $request->sort ?: 0,
            ]);

            $this->log('create', '创建角色', ['role_id' => $role->id]);

            return $this->success(null, '创建成功');
        } catch (\Exception $e) {
            return $this->error('创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示编辑角色表单
     */
    public function edit(Role $role): View
    {
        return $this->view('role.edit', compact('role'));
    }

    /**
     * 更新角色
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            'sort' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'status' => $request->status,
                'sort' => $request->sort ?: 0,
            ]);

            $this->log('update', '更新角色', ['role_id' => $role->id]);

            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除角色
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // 检查是否有管理员使用该角色
            if ($role->admins()->count() > 0) {
                return $this->error('该角色下还有管理员，无法删除');
            }

            $role->delete();

            $this->log('delete', '删除角色', ['role_id' => $role->id]);

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换角色状态
     */
    public function toggleStatus(Role $role): JsonResponse
    {
        try {
            $newStatus = $role->status === Role::STATUS_ENABLED
                ? Role::STATUS_DISABLED
                : Role::STATUS_ENABLED;

            $role->update(['status' => $newStatus]);

            $action = $newStatus === Role::STATUS_ENABLED ? '启用' : '禁用';
            $this->log('update', $action . '角色', ['role_id' => $role->id]);

            return $this->success(['status' => $newStatus], $action . '成功');
        } catch (\Exception $e) {
            return $this->error('操作失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示角色权限
     */
    public function permissions(Role $role): View|JsonResponse
    {
        if (request()->expectsJson()) {
            // 获取所有权限规则树
            $allRules = Rule::enabled()->ordered()->get();
            $roleRuleIds = $role->rules()->pluck('rule_id')->toArray();

            // 构建权限树
            $tree = $this->buildPermissionTree($allRules->toArray(), $roleRuleIds);

            return $this->success($tree);
        }

        return $this->view('role.permissions', compact('role'));
    }

    /**
     * 更新角色权限
     */
    public function updatePermissions(Request $request, Role $role): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rule_ids' => 'nullable|array',
            'rule_ids.*' => 'exists:rules,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $ruleIds = $request->rule_ids ?: [];
            $role->syncPermissions($ruleIds);

            // 清除权限缓存
            app(\App\Services\LPadmin\PermissionService::class)->clearAllPermissionCache();

            $this->log('update', '更新角色权限', [
                'role_id' => $role->id,
                'rule_count' => count($ruleIds)
            ]);

            return $this->success(null, '权限更新成功');
        } catch (\Exception $e) {
            return $this->error('权限更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 构建权限树
     */
    private function buildPermissionTree(array $rules, array $selectedIds, int $parentId = 0): array
    {
        $tree = [];

        foreach ($rules as $rule) {
            if ($rule['parent_id'] == $parentId) {
                $item = [
                    'id' => $rule['id'],
                    'title' => $rule['title'],
                    'name' => $rule['name'],
                    'type' => $rule['type'],
                    'checked' => in_array($rule['id'], $selectedIds),
                    'children' => $this->buildPermissionTree($rules, $selectedIds, $rule['id'])
                ];

                $tree[] = $item;
            }
        }

        return $tree;
    }
}
