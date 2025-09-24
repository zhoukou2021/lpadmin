<?php

namespace App\Http\Controllers\LPadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * 权限测试控制器
 * 用于测试权限错误提示功能
 */
class PermissionTestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:lpadmin');
    }

    /**
     * 测试需要config.manage权限的接口
     */
    public function testConfigManage(): JsonResponse
    {
        // 这个方法需要config.manage权限
        // 如果用户没有这个权限，会返回详细的权限错误信息
        
        return response()->json([
            'code' => 200,
            'message' => '成功访问需要config.manage权限的接口',
            'data' => [
                'permission_required' => 'config.manage',
                'access_granted' => true,
                'timestamp' => time()
            ]
        ]);
    }

    /**
     * 测试需要user.create权限的接口
     */
    public function testUserCreate(): JsonResponse
    {
        return response()->json([
            'code' => 200,
            'message' => '成功访问需要user.create权限的接口',
            'data' => [
                'permission_required' => 'user.create',
                'access_granted' => true,
                'timestamp' => time()
            ]
        ]);
    }

    /**
     * 测试需要admin.delete权限的接口
     */
    public function testAdminDelete(): JsonResponse
    {
        return response()->json([
            'code' => 200,
            'message' => '成功访问需要admin.delete权限的接口',
            'data' => [
                'permission_required' => 'admin.delete',
                'access_granted' => true,
                'timestamp' => time()
            ]
        ]);
    }

    /**
     * 测试需要cache.settings权限的接口
     */
    public function testCacheSettings(): JsonResponse
    {
        return response()->json([
            'code' => 200,
            'message' => '成功访问需要cache.settings权限的接口',
            'data' => [
                'permission_required' => 'cache.settings',
                'access_granted' => true,
                'timestamp' => time()
            ]
        ]);
    }

    /**
     * 获取权限测试页面
     */
    public function index()
    {
        return view('lpadmin.test.permission-test');
    }
}
