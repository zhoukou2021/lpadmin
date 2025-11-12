<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\LPadmin\User;

class UserController extends BaseController
{
    /**
     * 用户列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = User::query();

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

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // 分页
            $users = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 20));

            return $this->paginate($users);
        }

        return view('lpadmin.user.index');
    }

    /**
     * 创建用户页面
     */
    public function create(): View
    {
        return view('lpadmin.user.create');
    }

    /**
     * 保存用户
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'nickname' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'avatar' => 'nullable|string',
            'gender' => 'nullable|in:0,1,2',
            'birthday' => 'nullable|date',
        ];

        // 如果上传了头像文件，则验证文件
        if ($request->hasFile('avatar_file')) {
            $rules['avatar_file'] = 'image|mimes:jpeg,jpg,png,gif|max:2048';
        }

        $validator = Validator::make($request->all(), $rules, [
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码不一致',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'phone.unique' => '手机号已存在',
            'avatar_file.image' => '头像必须是图片文件',
            'avatar_file.mimes' => '头像格式必须是 jpeg、jpg、png 或 gif',
            'avatar_file.max' => '头像大小不能超过 2MB',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = $request->only([
                'username', 'nickname', 'email', 'phone',
                'avatar', 'gender', 'birthday', 'status', 'remark'
            ]);

            // 处理头像文件上传
            $avatar = null;
            if ($request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store('uploads/avatars', 'public');
                $avatar = '/storage/' . $path;
            }
            if ($avatar) {
                $data['avatar'] = $avatar;
            }

            $data['password'] = Hash::make($request->password);
            // 如果没有传递status，默认为启用
            if (!isset($data['status'])) {
                $data['status'] = 1;
            }

            $user = User::create($data);

            $this->log('create', '创建用户', ['user_id' => $user->id]);

            return $this->success($user, '创建成功');
        } catch (\Exception $e) {
            return $this->error('创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 显示用户详情
     */
    public function show(User $user): View
    {
        return view('lpadmin.user.show', compact('user'));
    }

    /**
     * 编辑用户页面
     */
    public function edit(User $user): View
    {
        return view('lpadmin.user.edit', compact('user'));
    }

    /**
     * 更新用户
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $rules = [
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'nickname' => 'required|string|max:50',
            'email' => 'nullable|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|string',
            'gender' => 'nullable|in:0,1,2',
            'birthday' => 'nullable|date',
        ];

        // 如果提供了密码，则验证密码
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        // 如果上传了头像文件，则验证文件
        if ($request->hasFile('avatar_file')) {
            $rules['avatar_file'] = 'image|mimes:jpeg,jpg,png,gif|max:2048';
        }

        $validator = Validator::make($request->all(), $rules, [
            'username.required' => '用户名不能为空',
            'username.unique' => '用户名已存在',
            'nickname.required' => '昵称不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'phone.unique' => '手机号已存在',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码不一致',
            'avatar_file.image' => '头像必须是图片文件',
            'avatar_file.mimes' => '头像格式必须是 jpeg、jpg、png 或 gif',
            'avatar_file.max' => '头像大小不能超过 2MB',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $avatar = null;
        if ($request->hasFile('avatar_file')) {
            $path = $request->file('avatar_file')->store('uploads/avatars', 'public');
            $avatar = '/storage/' . $path;
        }
        try {
            $data = $request->only([
                'username', 'nickname', 'email', 'phone',
                'avatar','gender', 'birthday','status','remark'
            ]);
            if ($avatar) {
                $data['avatar'] = $avatar;
            }
            // 如果提供了密码，则更新密码
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            $user->update($data);

            $this->log('update', '更新用户', ['user_id' => $user->id]);

            return $this->success($user, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除用户
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $user->delete();

            $this->log('delete', '删除用户', ['user_id' => $user->id]);

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 切换用户状态
     */
    public function toggleStatus(User $user): JsonResponse
    {
        try {
            $newStatus = $user->status === User::STATUS_ENABLED
                ? User::STATUS_DISABLED
                : User::STATUS_ENABLED;

            $user->update(['status' => $newStatus]);

            $this->log('update', '切换用户状态', ['user_id' => $user->id, 'status' => $newStatus]);

            return $this->success(null, '操作成功');
        } catch (\Exception $e) {
            return $this->error('操作失败: ' . $e->getMessage());
        }
    }


    /**
     * 批量删除用户
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $ids = $request->ids;
            $count = User::whereIn('id', $ids)->delete();

            $this->log('delete', '批量删除用户', ['count' => $count, 'ids' => $ids]);

            return $this->success(null, "成功删除 {$count} 个用户");
        } catch (\Exception $e) {
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取用户统计数据
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            // 基础统计
            'total_users' => User::count(),
            'enabled_users' => User::where('status', User::STATUS_ENABLED)->count(),
            'disabled_users' => User::where('status', User::STATUS_DISABLED)->count(),

            // 今日统计
            'today_new_users' => User::whereDate('created_at', today())->count(),
            'today_male_users' => User::whereDate('created_at', today())
                ->where('gender', User::GENDER_MALE)->count(),
            'today_female_users' => User::whereDate('created_at', today())
                ->where('gender', User::GENDER_FEMALE)->count(),

            // 昨日统计
            'yesterday_new_users' => User::whereDate('created_at', today()->subDay())->count(),
            'yesterday_male_users' => User::whereDate('created_at', today()->subDay())
                ->where('gender', User::GENDER_MALE)->count(),
            'yesterday_female_users' => User::whereDate('created_at', today()->subDay())
                ->where('gender', User::GENDER_FEMALE)->count(),

            // 本周统计
            'week_new_users' => User::whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->count(),
            'week_male_users' => User::whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->where('gender', User::GENDER_MALE)->count(),
            'week_female_users' => User::whereBetween('created_at', [
                now()->startOfWeek(), now()->endOfWeek()
            ])->where('gender', User::GENDER_FEMALE)->count(),

            // 性别分布
            'gender_distribution' => [
                'male' => User::where('gender', User::GENDER_MALE)->count(),
                'female' => User::where('gender', User::GENDER_FEMALE)->count(),
                'unknown' => User::where('gender', User::GENDER_UNKNOWN)->count(),
            ],

            // 最近登录统计
            'recent_login_users' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
            'never_login_users' => User::whereNull('last_login_at')->count(),
        ];

        return $this->success($stats, '获取统计数据成功');
    }
}
