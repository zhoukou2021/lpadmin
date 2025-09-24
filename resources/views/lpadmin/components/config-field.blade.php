{{--
配置字段组件
用于渲染不同类型的配置表单字段

参数说明：
- $config: 配置对象，包含name、title、value、type、options等属性
- $name: 字段名称（可选，默认使用$config->name）
- $value: 字段值（可选，默认使用$config->value）
- $required: 是否必填（可选，默认false）
- $disabled: 是否禁用（可选，默认false）
- $class: 额外的CSS类（可选）

使用示例：
@include('lpadmin.components.config-field', ['config' => $config])
@include('lpadmin.components.config-field', ['config' => $config, 'required' => true])
--}}

@php
    $name = $name ?? $config->name;
    $value = $value ?? $config->value;
    $type = $config->type;
    $title = $config->title;
    $description = $config->description;
    $options = $config->options_array ?? [];
    $required = $required ?? false;
    $disabled = $disabled ?? false;
    $class = $class ?? '';
    $fieldId = 'field_' . str_replace(['.', '[', ']'], '_', $name);
@endphp

<div class="layui-form-item {{ $class }}">
    <label class="layui-form-label {{ $required ? 'required' : '' }}">
        {{ $title }}
    </label>
    <div class="layui-input-block">
        @switch($type)
            {{-- 文本框 --}}
            @case('text')
                <input type="text" 
                       name="{{ $name }}" 
                       value="{{ $value }}" 
                       placeholder="请输入{{ $title }}"
                       class="layui-input"
                       {{ $required ? 'lay-verify=required' : '' }}
                       {{ $disabled ? 'disabled' : '' }}>
                @break

            {{-- 文本域 --}}
            @case('textarea')
                <textarea name="{{ $name }}" 
                          placeholder="请输入{{ $title }}"
                          class="layui-textarea"
                          rows="4"
                          {{ $required ? 'lay-verify=required' : '' }}
                          {{ $disabled ? 'disabled' : '' }}>{{ $value }}</textarea>
                @break

            {{-- 数字输入框 --}}
            @case('number')
                <input type="number" 
                       name="{{ $name }}" 
                       value="{{ $value }}" 
                       placeholder="请输入{{ $title }}"
                       class="layui-input"
                       {{ $required ? 'lay-verify=required' : '' }}
                       {{ $disabled ? 'disabled' : '' }}>
                @break

            {{-- 下拉选择 --}}
            @case('select')
                <select name="{{ $name }}" 
                        {{ $required ? 'lay-verify=required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}>
                    @if(!$required)
                        <option value="">请选择{{ $title }}</option>
                    @endif
                    @foreach($options as $optValue => $optLabel)
                        <option value="{{ $optValue }}" {{ $value == $optValue ? 'selected' : '' }}>
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </select>
                @break

            {{-- 单选框 --}}
            @case('radio')
                @foreach($options as $optValue => $optLabel)
                    <input type="radio" 
                           name="{{ $name }}" 
                           value="{{ $optValue }}" 
                           title="{{ $optLabel }}"
                           {{ $value == $optValue ? 'checked' : '' }}
                           {{ $disabled ? 'disabled' : '' }}>
                @endforeach
                @break

            {{-- 复选框 --}}
            @case('checkbox')
                @php
                    $selectedValues = is_array($value) ? $value : explode(',', $value);
                @endphp
                @foreach($options as $optValue => $optLabel)
                    <input type="checkbox" 
                           name="{{ $name }}[]" 
                           value="{{ $optValue }}" 
                           title="{{ $optLabel }}"
                           {{ in_array($optValue, $selectedValues) ? 'checked' : '' }}
                           {{ $disabled ? 'disabled' : '' }}>
                @endforeach
                @break

            {{-- 开关 --}}
            @case('switch')
                <input type="checkbox" 
                       name="{{ $name }}" 
                       lay-skin="switch" 
                       lay-text="开启|关闭" 
                       value="1"
                       {{ $value ? 'checked' : '' }}
                       {{ $disabled ? 'disabled' : '' }}>
                @break

            {{-- 图片上传 --}}
            @case('image')
                <div class="layui-upload">
                    <button type="button" 
                            class="layui-btn" 
                            id="upload-{{ $fieldId }}"
                            {{ $disabled ? 'disabled' : '' }}>
                        <i class="layui-icon layui-icon-upload"></i> 选择图片
                    </button>
                    <input type="hidden" 
                           name="{{ $name }}" 
                           value="{{ $value }}" 
                           id="input-{{ $fieldId }}">
                    <div class="layui-upload-list">
                        @if($value)
                            <img src="{{ $value }}" 
                                 class="layui-upload-img" 
                                 id="preview-{{ $fieldId }}"
                                 style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px;">
                        @else
                            <img src="" 
                                 class="layui-upload-img" 
                                 id="preview-{{ $fieldId }}"
                                 style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px; display: none;">
                        @endif
                    </div>
                </div>
                @break

            {{-- 文件上传 --}}
            @case('file')
                <div class="layui-upload">
                    <button type="button" 
                            class="layui-btn" 
                            id="upload-{{ $fieldId }}"
                            {{ $disabled ? 'disabled' : '' }}>
                        <i class="layui-icon layui-icon-upload"></i> 选择文件
                    </button>
                    <input type="hidden" 
                           name="{{ $name }}" 
                           value="{{ $value }}" 
                           id="input-{{ $fieldId }}">
                    <div class="layui-upload-list">
                        <div id="filename-{{ $fieldId }}" style="margin-top: 10px;">
                            {{ $value ? basename($value) : '未选择文件' }}
                        </div>
                    </div>
                </div>
                @break

            {{-- 颜色选择器 --}}
            @case('color')
                <div style="display: flex; align-items: center;">
                    <input type="text" 
                           name="{{ $name }}" 
                           value="{{ $value }}" 
                           class="layui-input" 
                           id="color-{{ $fieldId }}"
                           style="width: 200px;"
                           {{ $required ? 'lay-verify=required' : '' }}
                           {{ $disabled ? 'disabled' : '' }}>
                    <div id="preview-{{ $fieldId }}" 
                         style="width: 30px; height: 30px; border: 1px solid #e6e6e6; border-radius: 4px; margin-left: 10px; background-color: {{ $value }};"></div>
                </div>
                @break

            {{-- 日期选择器 --}}
            @case('date')
                <input type="text" 
                       name="{{ $name }}" 
                       value="{{ $value }}" 
                       class="layui-input" 
                       id="date-{{ $fieldId }}"
                       placeholder="请选择{{ $title }}"
                       readonly
                       {{ $required ? 'lay-verify=required' : '' }}
                       {{ $disabled ? 'disabled' : '' }}>
                @break

            {{-- 日期时间选择器 --}}
            @case('datetime')
                <input type="text" 
                       name="{{ $name }}" 
                       value="{{ $value }}" 
                       class="layui-input" 
                       id="datetime-{{ $fieldId }}"
                       placeholder="请选择{{ $title }}"
                       readonly
                       {{ $required ? 'lay-verify=required' : '' }}
                       {{ $disabled ? 'disabled' : '' }}>
                @break

            {{-- 默认文本框 --}}
            @default
                <input type="text" 
                       name="{{ $name }}" 
                       value="{{ $value }}" 
                       placeholder="请输入{{ $title }}"
                       class="layui-input"
                       {{ $required ? 'lay-verify=required' : '' }}
                       {{ $disabled ? 'disabled' : '' }}>
        @endswitch

        {{-- 描述信息 --}}
        @if($description)
            <div class="layui-form-mid layui-word-aux" style="margin-top: 5px;">
                {{ $description }}
            </div>
        @endif
    </div>
</div>

{{-- 初始化脚本 --}}
@push('scripts')
<script>
layui.use(['upload', 'laydate', 'colorpicker'], function() {
    const upload = layui.upload;
    const laydate = layui.laydate;
    const colorpicker = layui.colorpicker;

    @if($type === 'image' && !$disabled)
        // 图片上传
        upload.render({
            elem: '#upload-{{ $fieldId }}',
            url: '/lpadmin/upload',
            accept: 'images',
            done: function(res) {
                if (res.code === 0) {
                    $('#input-{{ $fieldId }}').val(res.data.url);
                    $('#preview-{{ $fieldId }}').attr('src', res.data.url).show();
                } else {
                    layer.msg(res.message, {icon: 2});
                }
            }
        });
    @endif

    @if($type === 'file' && !$disabled)
        // 文件上传
        upload.render({
            elem: '#upload-{{ $fieldId }}',
            url: '/lpadmin/upload',
            done: function(res) {
                if (res.code === 0) {
                    $('#input-{{ $fieldId }}').val(res.data.url);
                    $('#filename-{{ $fieldId }}').text(res.data.original_name);
                } else {
                    layer.msg(res.message, {icon: 2});
                }
            }
        });
    @endif

    @if($type === 'color' && !$disabled)
        // 颜色选择器
        colorpicker.render({
            elem: '#color-{{ $fieldId }}',
            done: function(color) {
                $('#preview-{{ $fieldId }}').css('background-color', color);
            }
        });
    @endif

    @if($type === 'date' && !$disabled)
        // 日期选择器
        laydate.render({
            elem: '#date-{{ $fieldId }}',
            type: 'date'
        });
    @endif

    @if($type === 'datetime' && !$disabled)
        // 日期时间选择器
        laydate.render({
            elem: '#datetime-{{ $fieldId }}',
            type: 'datetime'
        });
    @endif
});
</script>
@endpush
