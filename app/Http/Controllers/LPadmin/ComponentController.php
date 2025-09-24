<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\LPadmin\ComponentManager;
use App\Services\LPadmin\ComponentRouteManager;

/**
 * 组件管理控制器
 */
class ComponentController extends BaseController
{
    /**
     * 显示组件列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            try {
                $components = ComponentManager::scanComponents();
                
                // 格式化数据
                $data = [];
                foreach ($components as $name => $component) {
                    $data[] = [
                        'name' => $name,
                        'title' => $component['title'] ?? $name,
                        'description' => $component['description'] ?? '',
                        'version' => $component['version'] ?? '1.0.0',
                        'author' => $component['author'] ?? '',
                        'status' => $component['status'],
                        'status_label' => $component['status'] === ComponentManager::STATUS_INSTALLED ? '已安装' : '未安装',
                        'is_complete' => $component['is_complete'],
                        'has_controller' => $component['has_controller'],
                        'has_routes' => $component['has_routes'],
                        'has_views' => $component['has_views'],
                        'installed_at' => $component['installed_at'],
                        'created_at' => $component['created_at'] ?? '',
                        'updated_at' => $component['updated_at'] ?? ''
                    ];
                }
                
                return response()->json([
                    'code' => 0,
                    'msg' => '',
                    'count' => count($data),
                    'data' => $data,
                ]);
                
            } catch (\Exception $e) {
                return $this->error('获取组件列表失败: ' . $e->getMessage());
            }
        }
        
        return view('lpadmin.component.index');
    }
    
    /**
     * 安装组件
     */
    public function install(Request $request): JsonResponse
    {
        $componentName = $request->input('name');
        
        if (!$componentName) {
            return $this->error('组件名称不能为空');
        }
        
        try {
            $result = ComponentManager::installComponent($componentName);
            
            if ($result) {
                $this->log('install', '安装组件', ['component' => $componentName]);
                return $this->success(null, '组件安装成功');
            } else {
                return $this->error('组件安装失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('组件安装失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 卸载组件
     */
    public function uninstall(Request $request): JsonResponse
    {
        $componentName = $request->input('name');
        
        if (!$componentName) {
            return $this->error('组件名称不能为空');
        }
        
        try {
            $result = ComponentManager::uninstallComponent($componentName);
            
            if ($result) {
                $this->log('uninstall', '卸载组件', ['component' => $componentName]);
                return $this->success(null, '组件卸载成功');
            } else {
                return $this->error('组件卸载失败');
            }
            
        } catch (\Exception $e) {
            return $this->error('组件卸载失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取组件详情
     */
    public function show(string $name): JsonResponse
    {
        try {
            $componentPath = base_path('app/Components/' . $name);
            $componentInfo = ComponentManager::getComponentInfo($componentPath);
            
            if (!$componentInfo) {
                return $this->error('组件不存在');
            }
            
            return $this->success($componentInfo);
            
        } catch (\Exception $e) {
            return $this->error('获取组件详情失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 刷新组件列表
     */
    public function refresh(): JsonResponse
    {
        try {
            // 清除组件缓存
            \Illuminate\Support\Facades\Cache::forget(ComponentManager::CACHE_KEY);
            
            // 自动发现组件路由
            ComponentRouteManager::autoDiscoverAndRegisterRoutes();
            
            // 清除路由缓存
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            
            $this->log('refresh', '刷新组件列表');
            
            return $this->success(null, '组件列表刷新成功');
            
        } catch (\Exception $e) {
            return $this->error('刷新组件列表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取组件统计信息
     */
    public function statistics(): JsonResponse
    {
        try {
            $components = ComponentManager::scanComponents();
            $routeStats = ComponentRouteManager::getRouteStats();
            
            $stats = [
                'total_components' => count($components),
                'installed_components' => count(array_filter($components, function($c) {
                    return $c['status'] === ComponentManager::STATUS_INSTALLED;
                })),
                'uninstalled_components' => count(array_filter($components, function($c) {
                    return $c['status'] === ComponentManager::STATUS_UNINSTALLED;
                })),
                'complete_components' => count(array_filter($components, function($c) {
                    return $c['is_complete'];
                })),
                'incomplete_components' => count(array_filter($components, function($c) {
                    return !$c['is_complete'];
                })),
                'route_stats' => $routeStats
            ];
            
            return $this->success($stats);
            
        } catch (\Exception $e) {
            return $this->error('获取统计信息失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证组件
     */
    public function validateComponent(string $name): JsonResponse
    {
        try {
            $validation = ComponentRouteManager::validateComponentRoute($name);
            
            return $this->success($validation);
            
        } catch (\Exception $e) {
            return $this->error('验证组件失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 批量操作组件
     */
    public function batchAction(Request $request): JsonResponse
    {
        $action = $request->input('action');
        $components = $request->input('components', []);
        
        if (!in_array($action, ['install', 'uninstall'])) {
            return $this->error('不支持的操作');
        }
        
        if (empty($components)) {
            return $this->error('请选择要操作的组件');
        }
        
        try {
            $successCount = 0;
            $failedComponents = [];
            
            foreach ($components as $componentName) {
                if ($action === 'install') {
                    $result = ComponentManager::installComponent($componentName);
                } else {
                    $result = ComponentManager::uninstallComponent($componentName);
                }
                
                if ($result) {
                    $successCount++;
                } else {
                    $failedComponents[] = $componentName;
                }
            }
            
            $message = "批量{$action}完成，成功: {$successCount}个";
            if (!empty($failedComponents)) {
                $message .= "，失败: " . implode(', ', $failedComponents);
            }
            
            $this->log('batch_' . $action, '批量操作组件', [
                'action' => $action,
                'components' => $components,
                'success_count' => $successCount,
                'failed_components' => $failedComponents
            ]);
            
            return $this->success([
                'success_count' => $successCount,
                'failed_components' => $failedComponents
            ], $message);
            
        } catch (\Exception $e) {
            return $this->error('批量操作失败: ' . $e->getMessage());
        }
    }
}
