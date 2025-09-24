<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\LPadmin\Admin;
use App\Models\LPadmin\Role;

/**
 * 管理员控制器
 */
class AdminController extends BaseController
{
    /**
     * 显示主页面
     */
    public function main(): View
    {
        return view('lpadmin.layouts.main');
    }

    /**
     * 显示管理员列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            $query = Admin::with('roles');

            // 搜索条件
            if ($request->filled('username')) {
                $query->where('username', 'like', '%' . $request->username . '%');
            }

            if ($request->filled('nickname')) {
                $query->where('nickname', 'like', '%' . $request->nickname . '%');
            }

            if ($request->filled('email')) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->filled('phone')) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 创建时间范围搜索
            if ($request->filled('created_at') && is_array($request->created_at)) {
                $dates = $request->created_at;
                if (!empty($dates[0])) {
                    $query->where('created_at', '>=', $dates[0] . ' 00:00:00');
                }
                if (!empty($dates[1])) {
                    $query->where('created_at', '<=', $dates[1] . ' 23:59:59');
                }
            }

            // 排序
            $field = $request->get('field', 'id');
            $order = $request->get('order', 'desc');
            $query->orderBy($field, $order);

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            $admins = $query->paginate($limit, ['*'], 'page', $page);

            // 格式化数据
            $data = [];
            foreach ($admins->items() as $admin) {
                $item = [
                    'id' => $admin->id,
                    'username' => $this->cleanUtf8($admin->username),
                    'nickname' => $this->cleanUtf8($admin->nickname),
                    'email' => $this->cleanUtf8($admin->email ?? ''),
                    'phone' => $this->cleanUtf8($admin->phone ?? ''),
                    'avatar' => $admin->avatar,
                    'avatar_url' => $admin->avatar_url,
                    'status' => $admin->status,
                    'status_label' => $admin->status ? '启用' : '禁用',
                    'created_at' => $admin->created_at ? $admin->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $admin->updated_at ? $admin->updated_at->format('Y-m-d H:i:s') : '',
                    'last_login_at' => $admin->last_login_at ? $admin->last_login_at->format('Y-m-d H:i:s') : '从未登录',
                    'roles' => $admin->roles->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $this->cleanUtf8($role->name),
                            'display_name' => $this->cleanUtf8($role->display_name)
                        ];
                    })->toArray()
                ];
                $data[] = $item;
            }

            return response()->json([
                'code' => 0,
                'msg' => '',
                'count' => $admins->total(),
                'data' => $data,
            ]);
        }

        return view('lpadmin.admin.index');
    }

    /**
     * 显示创建管理员表单
     */
    public function create(): View
    {
        return view('lpadmin.admin.create');
    }

    /**
     * 存储新管理员
     */
    public function store(Request $request): JsonResponse
    {
        // 简单处理：将roles字段转换为role_ids数组
        if ($request->has('roles')) {
            $roles = $request->roles;
            // 确保是数组格式
            if (!is_array($roles)) {
                $roles = is_string($roles) ? explode(',', $roles) : [$roles];
            }
            $request->merge(['role_ids' => array_filter($roles)]);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:admins,username',
            'password' => 'required|string|min:6|confirmed',
            'nickname' => 'required|string|max:50',
            'email' => 'nullable|email|max:100|unique:admins,email',
            'phone' => 'nullable|string|max:20|unique:admins,phone',
            'status' => 'required|in:0,1',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ], [
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码不一致',
            'nickname.required' => '昵称不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'phone.unique' => '手机号已存在',
            'role_ids.required' => '请选择角色',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $admin = Admin::create([
                'username' => $request->username,
                'password' => $request->password,
                'nickname' => $request->nickname,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ]);

            // 分配角色
            $admin->syncRoles($request->role_ids);

            $this->log('create', '创建管理员', ['admin_id' => $admin->id]);

            return $this->success(null, '创建成功');
        } catch (\Exception $e) {
            return $this->error('创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示管理员详情
     */
    public function show(Admin $admin): JsonResponse
    {
        $admin->load('roles');
        return $this->success($admin);
    }

    /**
     * 显示编辑管理员表单
     */
    public function edit(Admin $admin): View
    {
        $admin->load('roles');
        return view('lpadmin.admin.edit', compact('admin'));
    }

    /**
     * 更新管理员
     */
    public function update(Request $request, Admin $admin): JsonResponse
    {
        // 简单处理：将roles字段转换为role_ids数组
        if ($request->has('roles')) {
            $roles = $request->roles;
            // 确保是数组格式
            if (!is_array($roles)) {
                $roles = is_string($roles) ? explode(',', $roles) : [$roles];
            }
            $request->merge(['role_ids' => array_filter($roles)]);
        }

        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('admins', 'username')->ignore($admin->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'nickname' => 'required|string|max:50',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('admins', 'phone')->ignore($admin->id),
            ],
            'status' => 'required|in:0,1',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = [
                'username' => $request->username,
                'nickname' => $request->nickname,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            // 如果提供了密码，则更新密码
            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            $admin->update($data);

            // 同步角色
            $admin->syncRoles($request->role_ids);

            $this->log('update', '更新管理员', ['admin_id' => $admin->id]);

            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除管理员
     */
    public function destroy(Admin $admin): JsonResponse
    {
        try {
            // 不能删除自己
            if ($admin->id === $this->admin->id) {
                return $this->error('不能删除自己');
            }

            $admin->delete();

            $this->log('delete', '删除管理员', ['admin_id' => $admin->id]);

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换管理员状态
     */
    public function toggleStatus(Admin $admin): JsonResponse
    {
        try {
            // 不能禁用自己
            if ($this->admin && $admin->id === $this->admin->id && $admin->status === Admin::STATUS_ENABLED) {
                return $this->error('不能禁用自己');
            }

            $newStatus = $admin->status === Admin::STATUS_ENABLED
                ? Admin::STATUS_DISABLED
                : Admin::STATUS_ENABLED;

            $admin->update(['status' => $newStatus]);

            $action = $newStatus === Admin::STATUS_ENABLED ? '启用' : '禁用';
            $this->log('update', $action . '管理员', ['admin_id' => $admin->id]);

            return $this->success(['status' => $newStatus], $action . '成功');
        } catch (\Exception $e) {
            return $this->error('操作失败: ' . $e->getMessage());
        }
    }

    /**
     * 重置管理员密码
     */
    public function resetPassword(Request $request, Admin $admin): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => '密码不能为空',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码不一致',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $admin->update(['password' => $request->password]);

            $this->log('update', '重置管理员密码', ['admin_id' => $admin->id]);

            return $this->success(null, '密码重置成功');
        } catch (\Exception $e) {
            return $this->error('密码重置失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除管理员
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:admins,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $ids = $request->ids;

            // 不能删除自己
            if ($this->admin && in_array($this->admin->id, $ids)) {
                return $this->error('不能删除自己');
            }

            $count = Admin::whereIn('id', $ids)->delete();

            $this->log('delete', '批量删除管理员', ['count' => $count, 'ids' => $ids]);

            return $this->success(null, "成功删除 {$count} 个管理员");
        } catch (\Exception $e) {
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }
}
