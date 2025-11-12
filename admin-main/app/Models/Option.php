<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $table = 'wa_options';

    protected $fillable = [
        'name',
        'value',
    ];

    /**
     * 获取配置值（自动解析JSON）
     */
    public static function getValue(string $name, $default = null)
    {
        $option = self::where('name', $name)->first();
        
        if (!$option) {
            return $default;
        }
        
        $value = $option->value;
        
        // 尝试解析JSON
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        return $value;
    }

    /**
     * 设置配置值（自动转换为JSON）
     */
    public static function setValue(string $name, $value): bool
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        return self::updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        ) ? true : false;
    }

    /**
     * 批量获取配置
     */
    public static function getMultiple(array $names): array
    {
        $options = self::whereIn('name', $names)->get();
        $result = [];
        
        foreach ($options as $option) {
            $value = $option->value;
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result[$option->name] = $decoded;
            } else {
                $result[$option->name] = $value;
            }
        }
        
        return $result;
    }

    /**
     * 批量设置配置
     */
    public static function setMultiple(array $options): bool
    {
        try {
            foreach ($options as $name => $value) {
                self::setValue($name, $value);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除配置
     */
    public static function deleteOption(string $name): bool
    {
        return self::where('name', $name)->delete() > 0;
    }

    /**
     * 获取系统配置
     */
    public static function getSystemConfig(): array
    {
        return self::getValue('system_config', [
            'logo' => [
                'title' => 'Laravel Admin',
                'image' => '/admin/images/logo.png'
            ],
            'menu' => [
                'data' => '/admin/rule/get',
                'method' => 'GET',
                'accordion' => true,
                'collapse' => false,
                'control' => false,
                'controlWidth' => 500,
                'select' => '0',
                'async' => true
            ],
            'tab' => [
                'enable' => true,
                'keepState' => true,
                'preload' => false,
                'session' => true,
                'max' => '30',
                'index' => [
                    'id' => '0',
                    'href' => '/admin/index/dashboard',
                    'title' => '仪表盘'
                ]
            ],
            'theme' => [
                'defaultColor' => '2',
                'defaultMenu' => 'light-theme',
                'defaultHeader' => 'light-theme',
                'allowCustom' => true,
                'banner' => false
            ]
        ]);
    }
}
