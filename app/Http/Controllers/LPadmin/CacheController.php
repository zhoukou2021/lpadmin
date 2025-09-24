<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Helpers\ConfigHelper;
use App\Models\LPadmin\Option;

class CacheController extends BaseController
{
    /**
     * 缓存管理首页
     */
    public function index(): View
    {
        return view('lpadmin.cache.index');
    }

    /**
     * 获取缓存统计信息
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = \App\Services\CacheService::getCacheStats();
            return $this->success($stats);
        } catch (\Exception $e) {
            return $this->error('获取缓存统计失败: ' . $e->getMessage());
        }
    }

    /**
     * 清除指定类型缓存
     */
    public function clearByType(Request $request): JsonResponse
    {
        $type = $request->input('type');

        if (empty($type)) {
            return $this->error('缓存类型不能为空');
        }

        try {
            $result = \App\Services\CacheService::clearCache($type);

            if ($result['success']) {
                $this->log('system', '清除缓存', ['type' => $type]);
                return $this->success($result, $result['message']);
            } else {
                return $this->error($result['message']);
            }
        } catch (\Exception $e) {
            return $this->error('缓存清除失败: ' . $e->getMessage());
        }
    }

    /**
     * 清除所有缓存
     */
    public function clearAll(): JsonResponse
    {
        try {
            $result = \App\Services\CacheService::clearAllCache();

            $this->log('system', '清除所有缓存', $result['details']);

            if ($result['success']) {
                return $this->success($result, $result['message']);
            } else {
                return $this->error($result['message'], $result['details']);
            }
        } catch (\Exception $e) {
            return $this->error('缓存清除失败: ' . $e->getMessage());
        }
    }

    /**
     * 清除配置缓存
     */
    public function clearConfig(): JsonResponse
    {
        try {
            // 清除配置缓存
            ConfigHelper::clearCache();

            // 清除统计缓存
            Cache::forget('lpadmin_config_statistics');

            $this->log('system', '清除配置缓存', []);

            return $this->success(null, '配置缓存清除成功');
        } catch (\Exception $e) {
            return $this->error('配置缓存清除失败: ' . $e->getMessage());
        }
    }

    /**
     * 预热配置缓存
     */
    public function warmupConfig(): JsonResponse
    {
        try {
            $result = ConfigHelper::warmupCache();

            $this->log('system', '预热配置缓存', $result['details']);

            if ($result['success']) {
                return $this->success($result, $result['message']);
            } else {
                return $this->error($result['message']);
            }
        } catch (\Exception $e) {
            return $this->error('缓存预热失败: ' . $e->getMessage());
        }
    }

    /**
     * 缓存监控页面
     */
    public function monitor(): View
    {
        return view('lpadmin.cache.monitor');
    }

    /**
     * 获取缓存监控数据
     */
    public function monitorData(): JsonResponse
    {
        try {
            $data = \App\Services\CacheService::getMonitorData();
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('获取监控数据失败: ' . $e->getMessage());
        }
    }

    /**
     * 缓存设置页面
     */
    public function settings(): View
    {
        return view('lpadmin.cache.settings');
    }

    /**
     * 更新缓存设置
     */
    public function updateSettings(Request $request): JsonResponse
    {
        // 验证数据
        $rules = [
            'cache_driver' => 'required|in:file,redis,memcached,database',
            'cache_ttl' => 'required|integer|min:60|max:86400',
            'cache_prefix' => 'nullable|string|max:50',
            'cache_auto_cleanup' => 'nullable|in:0,1',
            'cache_cleanup_interval' => 'nullable|integer|min:300|max:86400',
            'cache_compression' => 'nullable|in:0,1',
            'cache_serializer' => 'nullable|in:php,json,igbinary',
        ];

        $messages = [
            'cache_driver.required' => '缓存驱动不能为空',
            'cache_driver.in' => '缓存驱动类型无效',
            'cache_ttl.required' => '缓存过期时间不能为空',
            'cache_ttl.min' => '缓存过期时间不能少于60秒',
            'cache_ttl.max' => '缓存过期时间不能超过86400秒',
            'cache_cleanup_interval.min' => '清理间隔不能少于300秒',
            'cache_cleanup_interval.max' => '清理间隔不能超过86400秒',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $data = $request->all();

            // 记录接收到的数据用于调试
            Log::info('缓存设置更新请求', ['data' => $data]);

            // 更新配置项到配置管理系统
            $updated = 0;
            $updateDetails = [];

            foreach ($data as $name => $value) {
                if (strpos($name, 'cache_') === 0) {
                    // 检查配置项是否存在
                    $existingConfig = Option::where('group', 'cache')
                                           ->where('name', $name)
                                           ->first();

                    if ($existingConfig) {
                        $result = Option::where('group', 'cache')
                                       ->where('name', $name)
                                       ->update(['value' => $value]);
                        if ($result) {
                            $updated++;
                            $updateDetails[] = "{$name}: {$existingConfig->value} -> {$value}";
                        }
                    } else {
                        $updateDetails[] = "{$name}: 配置项不存在";
                    }
                }
            }

            Log::info('缓存设置更新结果', [
                'updated_count' => $updated,
                'details' => $updateDetails
            ]);

            // 同步到配置管理系统缓存
            $this->syncToConfigSystem();

            // 清除配置缓存
            Cache::forget('config_cache');
            Artisan::call('config:clear');

            $this->log('update', '更新缓存设置', [
                'updated_count' => $updated,
                'settings' => $data
            ]);

            return $this->success([
                'updated_count' => $updated
            ], "设置保存成功，更新了 {$updated} 个配置项");

        } catch (\Exception $e) {
            return $this->error('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 同步缓存设置到配置管理系统
     */
    private function syncToConfigSystem(): void
    {
        try {
            // 获取所有缓存配置
            $cacheConfigs = Option::where('group', 'cache')->get();

            // 更新ConfigHelper缓存
            $configData = [];
            foreach ($cacheConfigs as $config) {
                $configData[$config->name] = $config->value;
            }

            // 存储到配置缓存中
            Cache::put('config_cache_cache', $configData, 3600);

        } catch (\Exception $e) {
            // 记录错误但不影响主流程
            Log::warning('缓存配置同步失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取缓存设置
     */
    public function getSettings(): JsonResponse
    {
        try {
            // 从配置管理系统获取缓存设置
            $cacheConfigs = Option::where('group', 'cache')->get();

            $settings = [];
            foreach ($cacheConfigs as $config) {
                $settings[$config->name] = $config->value;
            }

            return $this->success($settings, '获取成功');

        } catch (\Exception $e) {
            return $this->error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 测试缓存连接
     */
    public function testConnection(Request $request): JsonResponse
    {
        $driver = $request->input('driver', 'file');

        try {
            $success = false;
            $message = '';

            switch ($driver) {
                case 'file':
                    // 测试文件缓存
                    $testKey = 'test_connection_' . time();
                    $testValue = 'test_value';

                    Cache::store('file')->put($testKey, $testValue, 60);
                    $retrieved = Cache::store('file')->get($testKey);
                    Cache::store('file')->forget($testKey);

                    $success = ($retrieved === $testValue);
                    $message = $success ? '文件缓存连接正常' : '文件缓存连接失败';
                    break;

                case 'redis':
                    // 测试Redis连接
                    try {
                        $testKey = 'test_connection_' . time();
                        $testValue = 'test_value';

                        Cache::store('redis')->put($testKey, $testValue, 60);
                        $retrieved = Cache::store('redis')->get($testKey);
                        Cache::store('redis')->forget($testKey);

                        $success = ($retrieved === $testValue);
                        $message = $success ? 'Redis连接正常' : 'Redis连接失败';
                    } catch (\Exception $e) {
                        $success = false;
                        $message = 'Redis连接失败: ' . $e->getMessage();
                    }
                    break;

                case 'memcached':
                    // 测试Memcached连接
                    try {
                        $testKey = 'test_connection_' . time();
                        $testValue = 'test_value';

                        Cache::store('memcached')->put($testKey, $testValue, 60);
                        $retrieved = Cache::store('memcached')->get($testKey);
                        Cache::store('memcached')->forget($testKey);

                        $success = ($retrieved === $testValue);
                        $message = $success ? 'Memcached连接正常' : 'Memcached连接失败';
                    } catch (\Exception $e) {
                        $success = false;
                        $message = 'Memcached连接失败: ' . $e->getMessage();
                    }
                    break;

                case 'database':
                    // 测试数据库缓存
                    try {
                        $testKey = 'test_connection_' . time();
                        $testValue = 'test_value';

                        Cache::store('database')->put($testKey, $testValue, 60);
                        $retrieved = Cache::store('database')->get($testKey);
                        Cache::store('database')->forget($testKey);

                        $success = ($retrieved === $testValue);
                        $message = $success ? '数据库缓存连接正常' : '数据库缓存连接失败';
                    } catch (\Exception $e) {
                        $success = false;
                        $message = '数据库缓存连接失败: ' . $e->getMessage();
                    }
                    break;

                default:
                    $success = false;
                    $message = '不支持的缓存驱动: ' . $driver;
                    break;
            }

            if ($success) {
                return $this->success(['driver' => $driver], $message);
            } else {
                return $this->error($message);
            }

        } catch (\Exception $e) {
            return $this->error('缓存连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取缓存键列表
     */
    public function keys(Request $request): JsonResponse
    {
        try {
            $pattern = $request->input('pattern', '*');
            $limit = $request->input('limit', 20);
            $page = $request->input('page', 1);

            $result = \App\Services\CacheService::getKeys($pattern, $limit);

            // 返回layui table期望的格式
            return response()->json([
                'code' => 0,
                'msg' => '',
                'count' => $result['total'],
                'data' => $result['keys']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'msg' => '获取缓存键失败: ' . $e->getMessage(),
                'count' => 0,
                'data' => []
            ]);
        }
    }

    /**
     * 删除指定缓存键
     */
    public function deleteKey(Request $request): JsonResponse
    {
        $key = $request->input('key');

        if (empty($key)) {
            return $this->error('缓存键不能为空');
        }

        try {
            $result = \App\Services\CacheService::deleteCacheKey($key);

            $this->log('delete', '删除缓存键', ['key' => $key]);
            return $this->success(['success' => $result], '缓存键删除成功');
        } catch (\Exception $e) {
            return $this->error('删除缓存键失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取缓存值
     */
    public function getValue(Request $request): JsonResponse
    {
        $key = $request->input('key');

        if (empty($key)) {
            return $this->error('缓存键不能为空');
        }

        try {
            $result = \App\Services\CacheService::getCacheValue($key);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error('获取缓存值失败: ' . $e->getMessage());
        }
    }

    /**
     * 设置缓存值
     */
    public function setValue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'value' => 'required',
            'ttl' => 'nullable|integer|min:60',
        ], [
            'key.required' => '缓存键不能为空',
            'value.required' => '缓存值不能为空',
            'ttl.min' => 'TTL不能少于60秒',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $key = $request->input('key');
            $value = $request->input('value');
            $ttl = $request->input('ttl', 3600);

            $result = \App\Services\CacheService::setValue($key, $value, $ttl);

            if ($result['success']) {
                $this->log('create', '设置缓存值', ['key' => $key, 'ttl' => $ttl]);
                return $this->success($result, '缓存值设置成功');
            } else {
                return $this->error($result['message']);
            }
        } catch (\Exception $e) {
            return $this->error('设置缓存值失败: ' . $e->getMessage());
        }
    }
}
