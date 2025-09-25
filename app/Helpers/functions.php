<?php

use App\Helpers\DictionaryHelper;

if (!function_exists('dict_data')) {
    /**
     * 获取字典数据
     */
    function dict_data(string $name, bool $enabledOnly = true): array
    {
        return DictionaryHelper::getDictData($name, $enabledOnly);
    }
}

if (!function_exists('dict_options')) {
    /**
     * 获取字典选项（用于表单）
     */
    function dict_options(string $name, bool $enabledOnly = true): array
    {
        return DictionaryHelper::getDictOptions($name, $enabledOnly);
    }
}

if (!function_exists('dict_label')) {
    /**
     * 根据值获取标签
     */
    function dict_label(string $name, string $value): string
    {
        return DictionaryHelper::getDictLabel($name, $value);
    }
}

if (!function_exists('dict_color')) {
    /**
     * 根据值获取颜色
     */
    function dict_color(string $name, string $value): string
    {
        return DictionaryHelper::getDictColor($name, $value);
    }
}

if (!function_exists('dict_select_html')) {
    /**
     * 生成字典选项的HTML（用于select）
     */
    function dict_select_html(string $name, string $selected = '', string $placeholder = '请选择'): string
    {
        return DictionaryHelper::getDictSelectHtml($name, $selected, $placeholder);
    }
}

if (!function_exists('dict_radio_html')) {
    /**
     * 生成字典选项的HTML（用于radio）
     */
    function dict_radio_html(string $name, string $fieldName, string $selected = '', array $attributes = []): string
    {
        return DictionaryHelper::getDictRadioHtml($name, $fieldName, $selected, $attributes);
    }
}

if (!function_exists('dict_checkbox_html')) {
    /**
     * 生成字典选项的HTML（用于checkbox）
     */
    function dict_checkbox_html(string $name, string $fieldName, array $selected = [], array $attributes = []): string
    {
        return DictionaryHelper::getDictCheckboxHtml($name, $fieldName, $selected, $attributes);
    }
}

if (!function_exists('dict_badge_html')) {
    /**
     * 生成带颜色的标签HTML
     */
    function dict_badge_html(string $name, string $value, string $defaultLabel = ''): string
    {
        return DictionaryHelper::getDictBadgeHtml($name, $value, $defaultLabel);
    }
}

if (!function_exists('dict_js_array')) {
    /**
     * 获取字典的JavaScript数组格式
     */
    function dict_js_array(string $name, bool $enabledOnly = true): string
    {
        return DictionaryHelper::getDictJsArray($name, $enabledOnly);
    }
}

if (!function_exists('dict_js_options')) {
    /**
     * 获取字典选项的JavaScript对象格式
     */
    function dict_js_options(string $name, bool $enabledOnly = true): string
    {
        return DictionaryHelper::getDictJsOptions($name, $enabledOnly);
    }
}
if (!function_exists('lpadmin_route_prefix')) {
    /**
     * 获取LPadmin后台路由前缀（不带斜杠）
     */
    function lpadmin_route_prefix(): string
    {
        return config('lpadmin.route.prefix', 'lpadmin');
    }
}

if (!function_exists('lpadmin_url_prefix')) {
    /**
     * 获取LPadmin后台URL前缀（带前导斜杠）
     */
    function lpadmin_url_prefix(): string
    {
        return '/' . lpadmin_route_prefix();
    }
}
if (!function_exists('shellToRegex')) {
    // 将shell通配符模式转换为正则表达式
    function shellToRegex($pattern) {
        $pattern = str_replace('.', '\.', $pattern);
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = str_replace('?', '.', $pattern);
        return '/^' . $pattern . '$/';
    }
}

