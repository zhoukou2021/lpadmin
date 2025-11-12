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
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="system-form">
                @php
                    $groupedConfigs = $systemConfigs->groupBy('group');
                    $groups = $groupedConfigs->keys()->values();
                    $groupTitles = [
                        'system' => '系统配置',
                        'security' => '安全配置',
                        'upload' => '上传配置',
                        'mail' => '邮件配置',
                        'cache' => '缓存配置',
                    ];
                @endphp

                <div class="layui-tab" lay-filter="config-groups">
                    <ul class="layui-tab-title">
                        @foreach($groups as $index => $group)
                            <li class="{{ $index === 0 ? 'layui-this' : '' }}">{{ $groupTitles[$group] ?? (ucfirst($group) . '配置') }}</li>
                        @endforeach
                    </ul>
                    <div class="layui-tab-content">
                        @foreach($groups as $index => $group)
                        <div class="layui-tab-item {{ $index === 0 ? 'layui-show' : '' }}">
                            <div class="config-form">
                                @foreach($groupedConfigs[$group] as $config)
                                <div class="layui-form-item">
                                    <label class="layui-form-label">
                                        {{ $config->title }}
                                        <div class="config-name">{{ $config->name }}</div>
                                    </label>
                                    <div class="layui-input-block">
                                        @if($config->is_i18n && in_array($config->type, ['text', 'textarea', 'richtext']))
                                            @php
                                                // 获取多语言列表（从名为 'lang' 的配置项）
                                                $langOption = $systemConfigs->where('name', 'lang')->first();
                                                $langs = [];
                                                if ($langOption && $langOption->value) {
                                                    $langs = explode(',', $langOption->value);
                                                } else {
                                                    $langs = ['zh_CN', 'en']; // 默认语言
                                                }
                                                // 解析当前配置的多语言值，格式：{"zh_CN":"内容","en":"内容"}
                                                $i18nValues = [];
                                                if (!empty($config->value)) {
                                                    try {
                                                        $decoded = json_decode($config->value, true);
                                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                            $i18nValues = $decoded;
                                                        } else {
                                                            // 如果 value 不是 JSON，尝试按旧格式兼容
                                                            $i18nValues = ['default' => $config->value];
                                                        }
                                                    } catch (\Exception $e) {
                                                        $i18nValues = ['default' => $config->value];
                                                    }
                                                }
                                                // 语言显示文本映射，优先读取 lang 配置的 options（JSON: {code:label}）
                                                $langLabels = [];
                                                if ($langOption && !empty($langOption->options)) {
                                                    $decodedLabels = json_decode($langOption->options, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedLabels)) {
                                                        $langLabels = $decodedLabels;
                                                    }
                                                }
                                            @endphp
                                            <div class="layui-tab" lay-filter="i18n-{{ $config->name }}">
                                                <ul class="layui-tab-title">
                                                    @foreach($langs as $lang)
                                                        <li class="{{ $loop->first ? 'layui-this' : '' }}">{{ $langLabels[$lang] ?? $lang }}</li>
                                                    @endforeach
                                                </ul>
                                                <div class="layui-tab-content">
                                                    @foreach($langs as $lang)
                                                        @if($config->type === 'text')
                                                            <div class="layui-tab-item {{ $loop->first ? 'layui-show' : '' }}">
                                                                <input type="text" name="{{ $config->name }}[{{ $lang }}]" value="{{ $i18nValues[$lang] ?? ($i18nValues['default'] ?? '') }}" class="layui-input">
                                                            </div>
                                                        @elseif($config->type === 'textarea')
                                                            <div class="layui-tab-item {{ $loop->first ? 'layui-show' : '' }}">
                                                                <textarea name="{{ $config->name }}[{{ $lang }}]" class="layui-textarea" rows="4">{{ $i18nValues[$lang] ?? ($i18nValues['default'] ?? '') }}</textarea>
                                                            </div>
                                                        @elseif($config->type === 'richtext')
                                                            <div class="layui-tab-item {{ $loop->first ? 'layui-show' : '' }}">
                                                                <textarea name="{{ $config->name }}[{{ $lang }}]" id="editor-{{ $config->name }}-{{ $lang }}" class="layui-textarea" rows="10">{!! $i18nValues[$lang] ?? ($i18nValues['default'] ?? '') !!}</textarea>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
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

                                        @case('richtext')
                                            <textarea name="{{ $config->name }}" id="editor-{{ $config->name }}" class="layui-textarea" rows="10">{!! $config->value !!}</textarea>
                                            @break
                                            
                                        @default
                                            <input type="text" name="{{ $config->name }}" value="{{ $config->value }}" class="layui-input">
                                            @endswitch
                                        @endif
                                        @if($config->description)
                                        <div class="config-description">{{ $config->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
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
        layui.use(['form', 'jquery', 'layer', 'upload', 'laydate', 'colorpicker', 'element'], function() {
            const form = layui.form;
            const $ = layui.jquery;
            const layer = layui.layer;
            const upload = layui.upload;
            const laydate = layui.laydate;
            const colorpicker = layui.colorpicker;
            const element = layui.element;

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
                @elseif($config->type === 'richtext' && !$config->is_i18n)
                    // 收集需要初始化的编辑器ID（非多语言）
                    if (!window.__LP_RT_EDITORS__) window.__LP_RT_EDITORS__ = [];
                    window.__LP_RT_EDITORS__.push('#editor-{{ $config->name }}');
                @elseif($config->is_i18n && in_array($config->type, ['text', 'textarea', 'richtext']))
                    // 收集多语言富文本编辑器ID
                    if (!window.__LP_RT_I18N_EDITORS__) window.__LP_RT_I18N_EDITORS__ = [];
                    @php
                        $langOption = $systemConfigs->where('name', 'lang')->first();
                        $langs = [];
                        if ($langOption && $langOption->value) {
                            $langs = explode(',', $langOption->value);
                        } else {
                            $langs = ['zh_CN', 'en'];
                        }
                    @endphp
                    @foreach($langs as $lang)
                        @if($config->type === 'richtext')
                            window.__LP_RT_I18N_EDITORS__.push('#editor-{{ $config->name }}-{{ $lang }}');
                        @endif
                    @endforeach
                @endif
            @endforeach

            // 初始化富文本编辑器（TinyMCE），一次性加载脚本，批量初始化
            (function initRichTextEditors(){
                var editors = window.__LP_RT_EDITORS__ || [];
                var i18nEditors = window.__LP_RT_I18N_EDITORS__ || [];
                var allEditors = editors.concat(i18nEditors);
                if (!allEditors.length) return;

                function init(base) {
                    if (!window.tinymce) return;
                    // 避免重复实例
                    try { tinymce.remove(allEditors.join(',')); } catch (e) {}
                    tinymce.init({
                        selector: allEditors.join(','),
                        language_url: (base ? base : (window.tinymce.baseURL || '')) + '/langs/zh_CN.js',
                        language: 'zh_CN',
                        menubar: false,
                        branding: false,
                        height: 380,
                        plugins: 'code preview link lists table image media fullscreen searchreplace autosave',
                        toolbar: 'undo redo | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat | code preview fullscreen',
                        convert_urls: false,
                        images_upload_handler: function (blobInfo, success, failure) {
                            success('data:' + blobInfo.blob().type + ';base64,' + blobInfo.base64());
                        }
                    });
                }

                // 简化加载：固定本地路径，失败回退 CDN
                (function loadTinySimple(){
                    var base = '/static/admin/tinymce';
                    function load(src, cb, onerr){ var s=document.createElement('script'); s.src=src; s.onload=cb; s.onerror=onerr; document.head.appendChild(s); }
                    if (!window.tinymce) {
                        load(base + '/tinymce.min.js', function(){ if (window.tinymce) window.tinymce.baseURL = base; init(base); }, function(){ load('https://cdn.staticfile.org/tinymce/6.8.3/tinymce.min.js', function(){ init(''); }); });
                    } else { init(window.tinymce.baseURL || base); }
                })();
            })();

            // 表单提交
            form.on('submit(submit)', function(data) {
                // 确保富文本内容同步到对应的 textarea
                if (window.tinymce && typeof tinymce.triggerSave === 'function') {
                    tinymce.triggerSave();
                }

                // 重新从表单读取数据，确保包含富文本最新值
                const arr = $('form[lay-filter="system-form"]').serializeArray();
                const field = {};
                
                arr.forEach(function(item){
                    // 处理带[]的复选框字段名，去除[]作为真正的key
                    const baseKey = item.name.replace(/\[]$/, '');
                    
                    if (item.name.endsWith('[]')) {
                        // 复选框字段
                        if (!field[baseKey]) {
                            field[baseKey] = [];
                        }
                        if (!Array.isArray(field[baseKey])) {
                            field[baseKey] = [field[baseKey]];
                        }
                        field[baseKey].push(item.value);
                    } else if (item.name.includes('[') && item.name.includes(']')) {
                        // 多语言字段：name[lang] 格式
                        const match = item.name.match(/^(.+?)\[(.+?)\]$/);
                        if (match) {
                            const realKey = match[1];
                            const lang = match[2];
                            if (!field[realKey]) {
                                field[realKey] = {};
                            }
                            field[realKey][lang] = item.value;
                        }
                    } else {
                        // 普通字段
                        if (field[baseKey] !== undefined) {
                            if (!Array.isArray(field[baseKey])) field[baseKey] = [field[baseKey]];
                            field[baseKey].push(item.value);
                        } else {
                            field[baseKey] = item.value;
                        }
                    }
                });
                
                // 处理复选框数组，转换为逗号分隔字符串
                // 处理多语言对象，转换为 JSON 字符串
                Object.keys(field).forEach(key => {
                    if (Array.isArray(field[key])) {
                        field[key] = field[key].join(',');
                    } else if (typeof field[key] === 'object' && field[key] !== null) {
                        // 多语言字段转换为 JSON
                        field[key] = JSON.stringify(field[key]);
                    }
                });

                // 处理开关：关闭时传 0，开启时传 1
                $('input[lay-skin="switch"]').each(function(){
                    const name = $(this).attr('name');
                    if (!name) return;
                    if ($(this).prop('checked')) {
                        field[name] = '1';
                    } else {
                        field[name] = '0';
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
