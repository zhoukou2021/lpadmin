<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>系统设置</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <style>
        .config-group {
            margin-bottom: 25px;
        }

        .config-group-title {
            background: #f8f9fa;
            padding: 8px 15px;
            border-left: 4px solid #1890ff;
            margin-bottom: 15px;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }

        .config-form {
            background: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 4px;
            padding: 20px;
        }

        .layui-form-item {
            margin-bottom: 15px;
        }

        .layui-form-label {
            width: 120px;
            padding: 9px 15px;
            font-weight: 500;
        }

        .layui-input-block {
            margin-left: 150px;
        }

        .config-description {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            line-height: 1.4;
        }

        .config-name {
            color: #999;
            font-size: 11px;
            font-family: 'Courier New', monospace;
            margin-top: 3px;
        }
        
        .layui-upload-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .color-preview {
            width: 30px;
            height: 30px;
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
</head>
<body class="pear-container">
    <div class="layui-card">
        <div class="layui-card-header">
            <h2>系统设置</h2>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="system-form">
                @php
                    $groupedConfigs = $systemConfigs->groupBy('group');
                @endphp
                
                @foreach($groupedConfigs as $group => $configs)
                <div class="config-group">
                    <div class="config-form">
                        @foreach($configs as $config)
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                {{ $config->title }}
                                <div class="config-name">{{ $config->name }}</div>
                            </label>
                            <div class="layui-input-block">
                                @switch($config->type)
                                    @case('text')
                                        <input type="text" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input">
                                        @break
                                        
                                    @case('textarea')
                                        <textarea name="{{ $config->name }}" class="layui-textarea" rows="4">{{ $config->value }}</textarea>
                                        @break
                                        
                                    @case('number')
                                        <input type="number" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input">
                                        @break
                                        
                                    @case('select')
                                        <select name="{{ $config->name }}">
                                            @php
                                                $options = $config->options_array;
                                            @endphp
                                            @foreach($options as $value => $label)
                                                <option value="{{ $value }}" {{ $config->value == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                        
                                    @case('radio')
                                        @php
                                            $options = $config->options_array;
                                        @endphp
                                        @foreach($options as $value => $label)
                                            <input type="radio" name="{{ $config->name }}" value="{{ $value }}" title="{{ $label }}" {{ $config->value == $value ? 'checked' : '' }}>
                                        @endforeach
                                        @break
                                        
                                    @case('checkbox')
                                        @php
                                            $options = $config->options_array;
                                            $selectedValues = is_array($config->value) ? $config->value : explode(',', $config->value);
                                        @endphp
                                        @foreach($options as $value => $label)
                                            <input type="checkbox" name="{{ $config->name }}[]" value="{{ $value }}" title="{{ $label }}" {{ in_array($value, $selectedValues) ? 'checked' : '' }}>
                                        @endforeach
                                        @break
                                        
                                    @case('switch')
                                        <input type="checkbox" name="{{ $config->name }}" lay-skin="switch" lay-text="开启|关闭" value="1" {{ $config->value ? 'checked' : '' }}>
                                        @break
                                        
                                    @case('image')
                                        <div class="layui-upload">
                                            <button type="button" class="layui-btn" id="upload-{{ $config->name }}">选择图片</button>
                                            <input type="hidden" name="{{ $config->name }}" value="{{ $config->value }}" id="input-{{ $config->name }}">
                                            @if($config->value)
                                                <img src="{{ $config->value }}" class="layui-upload-img" id="preview-{{ $config->name }}">
                                            @else
                                                <img src="" class="layui-upload-img" id="preview-{{ $config->name }}" style="display: none;">
                                            @endif
                                        </div>
                                        @break
                                        
                                    @case('file')
                                        <div class="layui-upload">
                                            <button type="button" class="layui-btn" id="upload-{{ $config->name }}">选择文件</button>
                                            <input type="hidden" name="{{ $config->name }}" value="{{ $config->value }}" id="input-{{ $config->name }}">
                                            <div class="layui-upload-list">
                                                <div id="filename-{{ $config->name }}">{{ $config->value ? basename($config->value) : '未选择文件' }}</div>
                                            </div>
                                        </div>
                                        @break
                                        
                                    @case('color')
                                        <input type="text" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input" id="color-{{ $config->name }}" style="width: 200px; display: inline-block;">
                                        <div class="color-preview" id="preview-{{ $config->name }}" style="background-color: {{ $config->value }};"></div>
                                        @break
                                        
                                    @case('date')
                                        <input type="text" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input" id="date-{{ $config->name }}" readonly>
                                        @break
                                        
                                    @case('datetime')
                                        <input type="text" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input" id="datetime-{{ $config->name }}" readonly>
                                        @break
                                        
                                    @default
                                        <input type="text" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input">
                                @endswitch
                                @if($config->description)
                                <div class="config-description">{{ $config->description }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                
                <div class="layui-form-item" style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="layui-btn layui-btn-lg" lay-submit lay-filter="submit">
                        <i class="layui-icon layui-icon-ok"></i> 保存设置
                    </button>
                    <button type="button" class="layui-btn layui-btn-primary layui-btn-lg" onclick="parent.layer.closeAll()">
                        <i class="layui-icon layui-icon-close"></i> 取消
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script src="/static/admin/js/radio-fix.js"></script>
    <script>
        layui.use(['form', 'jquery', 'layer', 'upload', 'laydate', 'colorpicker'], function() {
            const form = layui.form;
            const $ = layui.jquery;
            const layer = layui.layer;
            const upload = layui.upload;
            const laydate = layui.laydate;
            const colorpicker = layui.colorpicker;

            const SAVE_API = "{{ route('lpadmin.config.saveSystem') }}";

            // 初始化上传组件
            @foreach($systemConfigs as $config)
                @if($config->type === 'image')
                    upload.render({
                        elem: '#upload-{{ $config->name }}',
                        url: '/lpadmin/upload',
                        accept: 'images',
                        done: function(res) {
                            if (res.code === 0) {
                                $('#input-{{ $config->name }}').val(res.data.url);
                                $('#preview-{{ $config->name }}').attr('src', res.data.url).show();
                            } else {
                                layer.msg(res.message, {icon: 2});
                            }
                        }
                    });
                @elseif($config->type === 'file')
                    upload.render({
                        elem: '#upload-{{ $config->name }}',
                        url: '/lpadmin/upload',
                        done: function(res) {
                            if (res.code === 0) {
                                $('#input-{{ $config->name }}').val(res.data.url);
                                $('#filename-{{ $config->name }}').text(res.data.original_name);
                            } else {
                                layer.msg(res.message, {icon: 2});
                            }
                        }
                    });
                @elseif($config->type === 'color')
                    colorpicker.render({
                        elem: '#color-{{ $config->name }}',
                        done: function(color) {
                            $('#preview-{{ $config->name }}').css('background-color', color);
                        }
                    });
                @elseif($config->type === 'date')
                    laydate.render({
                        elem: '#date-{{ $config->name }}',
                        type: 'date'
                    });
                @elseif($config->type === 'datetime')
                    laydate.render({
                        elem: '#datetime-{{ $config->name }}',
                        type: 'datetime'
                    });
                @endif
            @endforeach

            // 表单提交
            form.on('submit(submit)', function(data) {
                const field = data.field;
                
                // 处理复选框数组
                Object.keys(field).forEach(key => {
                    if (Array.isArray(field[key])) {
                        field[key] = field[key].join(',');
                    }
                });

                // 提交数据
                $.ajax({
                    url: SAVE_API,
                    method: 'POST',
                    data: field,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (result) {
                        if (result.code === 0) {
                            layer.msg(result.message, {icon: 1}, function() {
                                parent.refreshTable && parent.refreshTable();
                                parent.layer.closeAll();
                            });
                        } else {
                            layer.msg(result.message, {icon: 2});
                        }
                    },
                    error: function () {
                        layer.msg('保存失败', {icon: 2});
                    }
                });

                return false;
            });

        });
    </script>

</body>
</html>
