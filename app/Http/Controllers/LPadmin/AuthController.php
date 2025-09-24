<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * 认证控制器
 *
 * 处理管理员登录、登出等认证相关功能
 */
class AuthController extends BaseController
{
    /**
     * 构造函数 - 排除认证中间件
     */
    public function __construct()
    {
        // 不调用父类构造函数，避免全局认证中间件
        $this->middleware('lpadmin.auth')->except(['showLogin', 'login', 'logout']);
        $this->middleware(function ($request, $next) {
            $this->admin = auth('lpadmin')->user();
            return $next($request);
        });
    }

    /**
     * 显示登录页面
     *
     * @return View|RedirectResponse
     */
    public function showLogin()
    {
        // 如果已经登录，重定向到后台首页
        if (Auth::guard('lpadmin')->check()) {
            return redirect()->route('lpadmin.dashboard.index');
        }

        return view('lpadmin.auth.login', [
            'system' => config('lpadmin.system'),
        ]);
    }

    /**
     * 处理登录请求
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'captcha' => 'required|string',
        ], [
            'username.required' => '请输入用户名',
            'password.required' => '请输入密码',
            'password.min' => '密码至少6位',
            'captcha.required' => '请输入验证码',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->error($validator->errors()->first());
            }
            return back()->withErrors($validator)->withInput();
        }

        // 验证验证码
        $captchaController = new \App\Http\Controllers\LPadmin\CaptchaController();
        if (!$captchaController->verify($request->captcha)) {
            $message = '验证码错误';
            if ($request->expectsJson()) {
                return $this->error($message);
            }
            return back()->withErrors(['captcha' => $message])->withInput();
        }

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
            'status' => 1, // 只允许启用的管理员登录
        ];

        if (Auth::guard('lpadmin')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // 记录登录日志
            $this->log('login', '管理员登录', [
                'username' => $request->username,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return $this->success([
                    'redirect' => route('lpadmin.index')
                ], '登录成功');
            }

            return redirect()->intended(route('lpadmin.index'));
        }

        $message = '用户名或密码错误';
        if ($request->expectsJson()) {
            return $this->error($message);
        }

        return back()->withErrors(['username' => $message])->withInput();
    }

    /**
     * 处理登出请求
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function logout(Request $request)
    {
        // 记录登出日志
        if (Auth::guard('lpadmin')->check()) {
            $this->log('logout', '管理员登出');
        }

        Auth::guard('lpadmin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return $this->success(null, '登出成功');
        }

        return redirect()->route('lpadmin.login');
    }

    /**
     * 获取当前登录用户信息
     *
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        $admin = Auth::guard('lpadmin')->user();

        if (!$admin) {
            return $this->error('未登录', 401);
        }

        return $this->success([
            'id' => $admin->id,
            'username' => $admin->username,
            'nickname' => $admin->nickname,
            'avatar' => $admin->avatar,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'last_login_at' => $admin->last_login_at,
            'last_login_ip' => $admin->last_login_ip,
        ]);
    }

    /**
     * 获取当前用户权限
     */
    public function permissions(): JsonResponse
    {
        $admin = Auth::guard('lpadmin')->user();

        if (!$admin) {
            return $this->error('未登录', 401);
        }

        // 获取当前管理员的所有权限
        $permissions = [];

        // 如果是超级管理员，返回所有权限
        if ($admin->id === 1) {
            $permissions = ['*'];
        } else {
            // 获取管理员的角色权限
            $roles = $admin->roles()->with('rules')->get();
            foreach ($roles as $role) {
                foreach ($role->rules as $rule) {
                    if ($rule->status == 1) {
                        $permissions[] = $rule->name;
                    }
                }
            }
            $permissions = array_unique($permissions);
        }

        return $this->success([
            'data' => $permissions
        ]);
    }

    /**
     * 个人资料页面
     */
    public function profile(Request $request): View|JsonResponse|RedirectResponse
    {
        // 确保用户已登录
        $admin = auth('lpadmin')->user();
        if (!$admin) {
            if ($request->expectsJson()) {
                return $this->error('用户未登录', 401);
            }
            return redirect()->route('lpadmin.login');
        }

        if ($request->expectsJson()) {
            $adminWithRoles = $admin->load('roles');
            return $this->success($adminWithRoles);
        }

        return view('lpadmin.auth.profile', ['admin' => $admin->load('roles')]);
    }

    /**
     * 更新个人资料
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // 确保用户已登录
        $admin = auth('lpadmin')->user();
        if (!$admin) {
            return $this->error('用户未登录', 401);
        }

        $validator = Validator::make($request->all(), [
            'nickname' => 'required|string|max:50',
            'email' => 'nullable|email|max:100|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20|unique:admins,phone,' . $admin->id,
            'avatar' => 'nullable|string',
        ], [
            'nickname.required' => '昵称不能为空',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '邮箱已存在',
            'phone.unique' => '手机号已存在',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = $request->only(['nickname', 'email', 'phone', 'avatar']);
            $admin->update($data);

            $this->log('update', '更新个人资料', ['admin_id' => $admin->id]);

            return $this->success($admin, '资料更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 修改密码页面
     */
    public function showChangePassword(): View
    {
        return view('lpadmin.auth.change-password');
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request): JsonResponse
    {
        // 确保用户已登录
        $admin = auth('lpadmin')->user();
        if (!$admin) {
            return $this->error('用户未登录', 401);
        }

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'old_password.required' => '原密码不能为空',
            'password.required' => '新密码不能为空',
            'password.min' => '新密码至少6位',
            'password.confirmed' => '两次密码不一致',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            // 验证原密码
            if (!Hash::check($request->old_password, $admin->password)) {
                return $this->error('原密码不正确');
            }

            // 更新密码
            $admin->update(['password' => Hash::make($request->password)]);

            $this->log('update', '修改密码', ['admin_id' => $admin->id]);

            return $this->success(null, '密码修改成功');
        } catch (\Exception $e) {
            return $this->error('修改失败: ' . $e->getMessage());
        }
    }
}
