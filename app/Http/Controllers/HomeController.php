<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * 前端首页控制器
 */
class HomeController extends Controller
{
    /**
     * 显示首页
     *
     * @return View
     */
    public function index(): View
    {
        // 获取系统信息
        $systemInfo = $this->getSystemInfo();

        // 获取核心功能信息
        $coreFeatures = $this->getCoreFeatures();

        return view('home.index', compact('systemInfo', 'coreFeatures'));
    }

    /**
     * 获取核心功能信息
     *
     * @return array
     */
    private function getCoreFeatures(): array
    {
        return [
            [
                'title' => '用户管理',
                'description' => '完整的用户账户管理，支持用户信息维护、状态控制、批量操作等功能',
                'icon' => 'fas fa-users',
                'color' => 'primary',
                'features' => ['用户列表', '用户编辑', '状态管理', '批量操作']
            ],
            [
                'title' => '权限管理',
                'description' => '基于RBAC的权限管理系统，支持角色分配、权限控制、菜单管理等',
                'icon' => 'fas fa-shield-alt',
                'color' => 'success',
                'features' => ['角色管理', '权限分配', '菜单控制', '访问控制']
            ],
            [
                'title' => '系统配置',
                'description' => '灵活的系统配置管理，支持参数设置、字典管理、缓存控制等',
                'icon' => 'fas fa-cogs',
                'color' => 'info',
                'features' => ['参数配置', '字典管理', '缓存管理', '系统设置']
            ],
            [
                'title' => '日志管理',
                'description' => '完整的操作日志记录，支持日志查看、搜索、导出、清理等功能',
                'icon' => 'fas fa-file-alt',
                'color' => 'warning',
                'features' => ['操作日志', '登录日志', '日志搜索', '日志导出']
            ],
            [
                'title' => '组件管理',
                'description' => '模块化组件系统，支持组件安装、卸载、配置、状态管理等',
                'icon' => 'fas fa-puzzle-piece',
                'color' => 'secondary',
                'features' => ['组件安装', '组件配置', '状态管理', '依赖管理']
            ],
            [
                'title' => '文件管理',
                'description' => '强大的文件上传管理，支持多种格式、批量上传、文件预览等',
                'icon' => 'fas fa-folder-open',
                'color' => 'danger',
                'features' => ['文件上传', '文件管理', '图片预览', '批量操作']
            ]
        ];
    }

    /**
     * 获取系统信息
     *
     * @return array
     */
    private function getSystemInfo(): array
    {
        $systemConfig = config('lpadmin.system', []);

        return [
            'name' => $systemConfig['name'] ?? 'LPadmin管理系统',
            'version' => $systemConfig['version'] ?? '1.0.0',
            'description' => $systemConfig['description'] ?? '基于Laravel 10+和PearAdminLayui构建的现代化后台管理系统',
            'logo' => $systemConfig['logo'] ?? '/static/admin/images/logo.png',
            'copyright' => $systemConfig['copyright'] ?? 'LPadmin',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'contact' => $systemConfig['contact'] ?? [
                'email' => 'jiu-men@qq.com',
                'phone' => '+86 15737185084',
                'address' => '中国·北京',
            ],
            'social' => $systemConfig['social'] ?? [
                'github' => '#',
                'qq' => '446820025',
                'wechat' => 'Baron369',
            ],
            'features' => [
                '现代化架构' => '基于Laravel 10+框架，性能优异',
                '美观界面' => 'PearAdminLayui UI，界面美观',
                '权限系统' => '完整的RBAC权限管理系统',
                '响应式设计' => '支持PC、平板、手机多端访问',
                '高度可配置' => '支持动态配置，灵活调整',
                '安全可靠' => '多层安全防护，操作日志记录',
                '易于扩展' => '模块化设计，支持组件式开发',
                '开源免费' => 'MIT协议，完全开源免费使用',
            ],
            'tech_stack' => [
                '后端框架' => 'Laravel ' . app()->version(),
                'PHP版本' => 'PHP ' . PHP_VERSION,
                '前端框架' => 'PearAdminLayui',
                '数据库' => 'MySQL 8.0+',
                '缓存' => 'Redis 6.0+',
                '架构模式' => 'MVC + Service层',
            ],
        ];
    }


}
