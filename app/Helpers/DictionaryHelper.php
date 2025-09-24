<?php

namespace App\Helpers;

use App\Services\LPadmin\DictionaryService;
use Illuminate\Support\Facades\App;

class DictionaryHelper
{
    protected static $dictionaryService;

    /**
     * 获取字典服务实例
     */
    protected static function getDictionaryService(): DictionaryService
    {
        if (!static::$dictionaryService) {
            static::$dictionaryService = App::make(DictionaryService::class);
        }
        
        return static::$dictionaryService;
    }

    /**
     * 获取字典数据
     */
    public static function getDictData(string $name, bool $enabledOnly = true): array
    {
        return static::getDictionaryService()->getDictData($name, $enabledOnly);
    }

    /**
     * 获取字典选项（用于表单）
     */
    public static function getDictOptions(string $name, bool $enabledOnly = true): array
    {
        return static::getDictionaryService()->getDictOptions($name, $enabledOnly);
    }

    /**
     * 根据值获取标签
     */
    public static function getDictLabel(string $name, string $value): string
    {
        return static::getDictionaryService()->getDictLabel($name, $value);
    }

    /**
     * 根据值获取颜色
     */
    public static function getDictColor(string $name, string $value): string
    {
        return static::getDictionaryService()->getDictColor($name, $value);
    }

    /**
     * 生成字典选项的HTML（用于select）
     */
    public static function getDictSelectHtml(string $name, string $selected = '', string $placeholder = '请选择'): string
    {
        $options = static::getDictOptions($name);
        $html = '';
        
        if ($placeholder) {
            $html .= '<option value="">' . $placeholder . '</option>';
        }
        
        foreach ($options as $value => $label) {
            $selectedAttr = $value == $selected ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>' . htmlspecialchars($label) . '</option>';
        }
        
        return $html;
    }

    /**
     * 生成字典选项的HTML（用于radio）
     */
    public static function getDictRadioHtml(string $name, string $fieldName, string $selected = '', array $attributes = []): string
    {
        $options = static::getDictOptions($name);
        $html = '';
        
        foreach ($options as $value => $label) {
            $checkedAttr = $value == $selected ? ' checked' : '';
            $attrStr = '';
            
            foreach ($attributes as $attr => $attrValue) {
                $attrStr .= ' ' . $attr . '="' . htmlspecialchars($attrValue) . '"';
            }
            
            $html .= '<input type="radio" name="' . $fieldName . '" value="' . htmlspecialchars($value) . '" title="' . htmlspecialchars($label) . '"' . $checkedAttr . $attrStr . '>';
        }
        
        return $html;
    }

    /**
     * 生成字典选项的HTML（用于checkbox）
     */
    public static function getDictCheckboxHtml(string $name, string $fieldName, array $selected = [], array $attributes = []): string
    {
        $options = static::getDictOptions($name);
        $html = '';
        
        foreach ($options as $value => $label) {
            $checkedAttr = in_array($value, $selected) ? ' checked' : '';
            $attrStr = '';
            
            foreach ($attributes as $attr => $attrValue) {
                $attrStr .= ' ' . $attr . '="' . htmlspecialchars($attrValue) . '"';
            }
            
            $html .= '<input type="checkbox" name="' . $fieldName . '[]" value="' . htmlspecialchars($value) . '" title="' . htmlspecialchars($label) . '"' . $checkedAttr . $attrStr . '>';
        }
        
        return $html;
    }

    /**
     * 生成带颜色的标签HTML
     */
    public static function getDictBadgeHtml(string $name, string $value, string $defaultLabel = ''): string
    {
        $label = static::getDictLabel($name, $value);
        $color = static::getDictColor($name, $value);
        
        if ($label === $value && $defaultLabel) {
            $label = $defaultLabel;
        }
        
        return '<span class="layui-badge layui-bg-' . $color . '">' . htmlspecialchars($label) . '</span>';
    }

    /**
     * 获取字典的JavaScript数组格式
     */
    public static function getDictJsArray(string $name, bool $enabledOnly = true): string
    {
        $data = static::getDictData($name, $enabledOnly);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取字典选项的JavaScript对象格式
     */
    public static function getDictJsOptions(string $name, bool $enabledOnly = true): string
    {
        $options = static::getDictOptions($name, $enabledOnly);
        return json_encode($options, JSON_UNESCAPED_UNICODE);
    }
}
