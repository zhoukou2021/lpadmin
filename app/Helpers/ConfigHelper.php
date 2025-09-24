<?php

namespace App\Helpers;

use App\Models\LPadmin\Option;
use Illuminate\Support\Facades\Cache;

/**
 * 配置助手类
 * 提供便捷的配置获取和设置方法
 */
class ConfigHelper
{
    /**
     * 缓存前缀
     */
    const CACHE_PREFIX = 'lpadmin_config_';

    /**
     * 缓存时间（秒）
     */
    const CACHE_TTL = 3600;

    /**
     * 获取配置值
     *
     * @param string $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return Option::getValue($name, $default);
    }

    /**
     * 设置配置值
     *
     * @param string $name 配置名称
     * @param mixed $value 配置值
     * @return bool
     */
    public static function set(string $name, $value): bool
    {
        return Option::setValue($name, $value);
    }

    /**
     * 批量设置配置值
     *
     * @param array $configs 配置数组 ['name' => 'value', ...]
     * @return bool
     */
    public static function setMany(array $configs): bool
    {
        return Option::setValues($configs);
    }

    /**
     * 获取分组配置
     *
     * @param string $group 分组名称
     * @return array
     */
    public static function getGroup(string $group): array
    {
        return Option::getGroupValues($group);
    }

    /**
     * 获取系统配置
     *
     * @return array
     */
    public static function getSystemConfig(): array
    {
        return self::getGroup('system');
    }

    /**
     * 获取格式化的配置值
     *
     * @param string $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getFormatted(string $name, $default = null)
    {
        $option = Option::where('name', $name)->first();
        
        if (!$option) {
            return $default;
        }

        return $option->formatted_value;
    }

    /**
     * 检查配置是否存在
     *
     * @param string $name 配置名称
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return Option::where('name', $name)->exists();
    }

    /**
     * 删除配置
     *
     * @param string $name 配置名称
     * @return bool
     */
    public static function forget(string $name): bool
    {
        $option = Option::where('name', $name)->first();
        
        if ($option) {
            Option::clearCache($name);
            return $option->delete();
        }

        return false;
    }

    /**
     * 清除配置缓存
     *
     * @param string|null $name 配置名称，为空则清除所有
     * @return void
     */
    public static function clearCache(?string $name = null): void
    {
        Option::clearCache($name);

        // 清除统计缓存
        Cache::forget(self::CACHE_PREFIX . 'statistics');

        // 清除分组缓存
        if (!$name) {
            $groups = Option::distinct('group')->pluck('group');
            foreach ($groups as $group) {
                Cache::forget('lpadmin_options_group_' . $group);
            }
        }
    }

    /**
     * 预热配置缓存
     *
     * @return array
     */
    public static function warmupCache(): array
    {
        $result = [
            'success' => true,
            'message' => '',
            'details' => []
        ];

        try {
            // 预热所有配置项
            $options = Option::all();
            foreach ($options as $option) {
                Option::getValue($option->name);
                $result['details'][] = "预热配置: {$option->name}";
            }

            // 预热分组配置
            $groups = Option::distinct('group')->pluck('group');
            foreach ($groups as $group) {
                Option::getGroupValues($group);
                $result['details'][] = "预热分组: {$group}";
            }

            // 预热统计信息
            self::getStatistics();
            $result['details'][] = "预热统计信息";

            $result['message'] = '配置缓存预热成功，共预热 ' . count($result['details']) . ' 项';
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = '配置缓存预热失败: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * 获取所有配置分组
     *
     * @return array
     */
    public static function getGroups(): array
    {
        return Option::distinct('group')->pluck('group')->toArray();
    }

    /**
     * 获取配置类型列表
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return Option::$typeLabels;
    }

    /**
     * 验证配置类型
     *
     * @param string $type 配置类型
     * @return bool
     */
    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, Option::$typeLabels);
    }

    /**
     * 获取配置统计信息
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'statistics';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $total = Option::count();
            $groups = Option::distinct('group')->count();
            $systemConfigs = Option::where('group', 'system')->count();
            $customConfigs = $total - $systemConfigs;

            return [
                'total' => $total,
                'groups' => $groups,
                'system' => $systemConfigs,
                'custom' => $customConfigs,
            ];
        });
    }

    /**
     * 导出配置
     *
     * @param string|null $group 分组名称，为空则导出所有
     * @return array
     */
    public static function export(?string $group = null): array
    {
        $query = Option::query();
        
        if ($group) {
            $query->where('group', $group);
        }
        
        $options = $query->orderBy('group')->orderBy('sort')->get();
        
        return [
            'export_time' => now()->toDateTimeString(),
            'total_count' => $options->count(),
            'group' => $group,
            'configs' => $options->map(function ($option) {
                return [
                    'group' => $option->group,
                    'name' => $option->name,
                    'title' => $option->title,
                    'value' => $option->value,
                    'type' => $option->type,
                    'options' => $option->options,
                    'description' => $option->description,
                    'sort' => $option->sort,
                ];
            })->toArray()
        ];
    }

    /**
     * 导入配置
     *
     * @param array $data 配置数据
     * @param string $mode 导入模式 merge|replace
     * @return array
     */
    public static function import(array $data, string $mode = 'merge'): array
    {
        if (!isset($data['configs']) || !is_array($data['configs'])) {
            throw new \InvalidArgumentException('配置数据格式错误');
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($data['configs'] as $config) {
            try {
                if (!isset($config['name']) || !isset($config['group'])) {
                    $skipped++;
                    continue;
                }

                $existing = Option::where('name', $config['name'])->first();

                if ($existing) {
                    if ($mode === 'replace') {
                        $existing->update([
                            'group' => $config['group'] ?? $existing->group,
                            'title' => $config['title'] ?? $existing->title,
                            'value' => $config['value'] ?? $existing->value,
                            'type' => $config['type'] ?? $existing->type,
                            'options' => $config['options'] ?? $existing->options,
                            'description' => $config['description'] ?? $existing->description,
                            'sort' => $config['sort'] ?? $existing->sort,
                        ]);
                        Option::clearCache($existing->name);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    Option::create([
                        'group' => $config['group'],
                        'name' => $config['name'],
                        'title' => $config['title'] ?? $config['name'],
                        'value' => $config['value'] ?? '',
                        'type' => $config['type'] ?? 'text',
                        'options' => $config['options'] ?? null,
                        'description' => $config['description'] ?? null,
                        'sort' => $config['sort'] ?? 0,
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "配置 {$config['name']} 导入失败: " . $e->getMessage();
                $skipped++;
            }
        }

        // 清除统计缓存
        Cache::forget(self::CACHE_PREFIX . 'statistics');

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * 创建配置项
     *
     * @param array $data 配置数据
     * @return Option
     */
    public static function create(array $data): Option
    {
        $option = Option::create($data);
        
        // 清除统计缓存
        Cache::forget(self::CACHE_PREFIX . 'statistics');
        
        return $option;
    }

    /**
     * 更新配置项
     *
     * @param string $name 配置名称
     * @param array $data 配置数据
     * @return bool
     */
    public static function update(string $name, array $data): bool
    {
        $option = Option::where('name', $name)->first();
        
        if (!$option) {
            return false;
        }

        $result = $option->update($data);
        
        if ($result) {
            Option::clearCache($name);
            // 清除统计缓存
            Cache::forget(self::CACHE_PREFIX . 'statistics');
        }
        
        return $result;
    }

    /**
     * 获取网站基本信息
     *
     * @return array
     */
    public static function getSiteInfo(): array
    {
        return [
            'name' => self::get('site_name', 'LPadmin管理系统'),
            'logo' => self::get('site_logo', '/static/admin/images/logo.png'),
            'copyright' => self::get('site_copyright', 'Copyright © 2024 LPadmin'),
            'keywords' => self::get('site_keywords', ''),
            'description' => self::get('site_description', ''),
        ];
    }
}
