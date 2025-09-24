{{--
通用图标选择组件
使用方法：
@include('lpadmin.components.icon-picker', [
    'name' => 'icon',           // 字段名称
    'value' => $model->icon,    // 当前值
    'label' => '图标',          // 标签文本
    'placeholder' => '请选择图标', // 占位符
    'required' => false,        // 是否必填
    'help' => '点击输入框选择图标'  // 帮助文本
])
--}}

@php
    $name = $name ?? 'icon';
    $value = $value ?? '';
    $label = $label ?? '图标';
    $placeholder = $placeholder ?? '请选择图标';
    $required = $required ?? false;
    $help = $help ?? '点击输入框选择图标';
    $id = $id ?? $name;
@endphp

<div class="layui-form-item">
    <label class="layui-form-label{{ $required ? ' layui-form-required' : '' }}">{{ $label }}</label>
    <div class="layui-input-block">
        <input type="text" 
               name="{{ $name }}" 
               id="{{ $id }}" 
               value="{{ $value }}" 
               class="layui-input icon-picker-input" 
               placeholder="{{ $placeholder }}"
               {{ $required ? 'lay-verify="required"' : '' }}
               readonly>
        <div class="layui-form-mid layui-word-aux">
            {{ $help }}
            @if($value)
                <i class="layui-icon {{ $value }}" style="margin-left: 10px; font-size: 16px; color: #1890ff;"></i>
            @endif
        </div>
    </div>
</div>

<script>
// 图标选择器初始化函数
function initIconPicker(elementId, options = {}) {
    layui.use(['iconPicker'], function() {
        let iconPicker = layui.iconPicker;
        let $ = layui.$;
        
        // 默认配置
        const defaultOptions = {
            type: 'fontClass',
            page: true,
            limit: 12,
            search: true,
            click: function(data) {
                console.log('选择的图标:', data);
                // 更新预览图标
                let $input = $('#' + elementId);
                let $preview = $input.siblings('.layui-form-mid').find('i');
                
                if ($preview.length > 0) {
                    $preview.attr('class', 'layui-icon ' + data.icon);
                } else {
                    $input.siblings('.layui-form-mid').append(
                        '<i class="layui-icon ' + data.icon + '" style="margin-left: 10px; font-size: 16px; color: #1890ff;"></i>'
                    );
                }
            }
        };
        
        // 合并配置
        const config = Object.assign({}, defaultOptions, options, {
            elem: '#' + elementId
        });
        
        // 渲染图标选择器
        iconPicker.render(config);
    });
}

// 自动初始化当前页面的图标选择器
document.addEventListener('DOMContentLoaded', function() {
    // 如果layui已加载，直接初始化
    if (typeof layui !== 'undefined') {
        initIconPicker('{{ $id }}');
    } else {
        // 等待layui加载完成
        const checkLayui = setInterval(function() {
            if (typeof layui !== 'undefined') {
                clearInterval(checkLayui);
                initIconPicker('{{ $id }}');
            }
        }, 100);
    }
});
</script>

<style>
.icon-picker-input {
    cursor: pointer;
}

.icon-picker-input:hover {
    border-color: #1890ff;
}

.layui-form-mid i {
    transition: all 0.3s ease;
}

.layui-form-mid i:hover {
    transform: scale(1.2);
}
</style>
