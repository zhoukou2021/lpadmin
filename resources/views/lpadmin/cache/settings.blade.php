<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>缓存设置</title>
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

        .connection-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 10px;
        }

        .connection-success {
            background: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }

        .connection-error {
            background: #fff2f0;
            color: #ff4d4f;
            border: 1px solid #ffccc7;
        }
    </style>
</head>
<body class="pear-container">
    <div class="layui-card">
        <div class="layui-card-header">
            <h2>缓存设置</h2>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="cache-settings-form">

                <!-- 基础设置 -->
                <div class="config-group">
                    <div class="config-group-title">基础设置</div>
                    <div class="config-form">
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                缓存驱动
                                <div class="config-name">cache_driver</div>
                            </label>
                            <div class="layui-input-block">
                                <select name="cache_driver" lay-filter="cache-driver">
                                    <option value="file">文件缓存</option>
                                    <option value="redis">Redis</option>
                                    <option value="memcached">Memcached</option>
                                    <option value="database">数据库</option>
                                </select>
                                <div class="config-description">选择缓存存储驱动类型</div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                默认TTL
                                <div class="config-name">cache_ttl</div>
                            </label>
                            <div class="layui-input-block">
                                <input type="number" name="cache_ttl" placeholder="3600" class="layui-input" min="60" max="86400" value="3600">
                                <div class="config-description">缓存默认过期时间，单位：秒（60-86400秒）</div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                缓存前缀
                                <div class="config-name">cache_prefix</div>
                            </label>
                            <div class="layui-input-block">
                                <input type="text" name="cache_prefix" placeholder="lpadmin_" class="layui-input" maxlength="50">
                                <div class="config-description">用于区分不同应用的缓存，避免键名冲突</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 自动清理设置 -->
                <div class="config-group">
                    <div class="config-group-title">自动清理设置</div>
                    <div class="config-form">
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                启用自动清理
                                <div class="config-name">cache_auto_clear</div>
                            </label>
                            <div class="layui-input-block">
                                <input type="checkbox" name="cache_auto_clear" lay-skin="switch" lay-text="开启|关闭">
                                <div class="config-description">启用后系统将定期清理过期缓存</div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                清理间隔
                                <div class="config-name">cache_cleanup_interval</div>
                            </label>
                            <div class="layui-input-block">
                                <input type="number" name="cache_cleanup_interval" placeholder="3600" class="layui-input" min="300" max="86400" value="3600">
                                <div class="config-description">自动清理的执行间隔，单位：秒（300-86400秒）</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 性能优化设置 -->
                <div class="config-group">
                    <div class="config-group-title">性能优化设置</div>
                    <div class="config-form">
                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                启用缓存压缩
                                <div class="config-name">cache_enable_compression</div>
                            </label>
                            <div class="layui-input-block">
                                <input type="checkbox" name="cache_enable_compression" lay-skin="switch" lay-text="开启|关闭">
                                <div class="config-description">启用后将压缩缓存数据，节省存储空间</div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">
                                缓存序列化
                                <div class="config-name">cache_serializer</div>
                            </label>
                            <div class="layui-input-block">
                                <select name="cache_serializer">
                                    <option value="php">PHP序列化</option>
                                    <option value="json">JSON序列化</option>
                                    <option value="igbinary">Igbinary序列化</option>
                                </select>
                                <div class="config-description">选择缓存数据的序列化方式</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="layui-form-item" style="margin-top: 30px;">
                    <div class="layui-input-block">
                        <button class="layui-btn pear-btn-primary" lay-submit lay-filter="save-settings">
                            <i class="layui-icon layui-icon-ok"></i> 保存设置
                        </button>
                        <button type="button" class="layui-btn layui-btn-normal" id="test-connection">
                            <i class="layui-icon layui-icon-link"></i> 测试连接
                        </button>
                        <button type="button" class="layui-btn layui-btn-primary" id="reset-form">
                            <i class="layui-icon layui-icon-refresh"></i> 重置
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script src="/static/admin/component/layui/layui.js"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>
layui.use(['form', 'layer', 'jquery'], function(){
    const form = layui.form;
    const layer = layui.layer;
    const $ = layui.jquery;

    // 设置CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 页面加载时获取设置
    loadSettings();

    // 加载设置
    function loadSettings() {
        const loadIndex = layer.load(2, {content: '加载中...'});

        $.get('{{ route("lpadmin.cache.getSettings") }}', function(res) {
            layer.close(loadIndex);

            if (res.code === 0) {
                const settings = res.data;
                // 填充表单数据
                Object.keys(settings).forEach(key => {
                    const input = $('[name="' + key + '"]');
                    if (input.length) {
                        if (input.attr('lay-skin') === 'switch') {
                            // 处理 switch 开关组件
                            input.prop('checked', settings[key] == '1' || settings[key] === true || settings[key] === 'true' || settings[key] === 'on');
                        } else if (input.attr('type') === 'checkbox') {
                            input.prop('checked', settings[key] == '1' || settings[key] === true || settings[key] === 'true');
                        } else {
                            input.val(settings[key]);
                        }
                    }
                });

                // 重新渲染表单
                form.render();
            } else {
                layer.msg('加载设置失败：' + (res.message || '未知错误'), {icon: 2});
            }
        }).fail(function(xhr) {
            layer.close(loadIndex);
            layer.msg('加载设置失败：网络错误', {icon: 2});
        });
    }

    // 缓存驱动变化时的处理
    form.on('select(cache-driver)', function(data) {
        const driver = data.value;
        updateConnectionStatus('');
        console.log('选择的缓存驱动：', driver);
    });

    // 保存设置
    form.on('submit(save-settings)', function(data) {
        const loadIndex = layer.load(2, {content: '保存中...'});

        // 处理开关数据格式转换
        const formData = data.field;

        // 将 switch 组件的 "on"/undefined 转换为 true/false
        formData.cache_auto_clear = formData.cache_auto_clear === 'on' ? '1' : '0';
        formData.cache_enable_compression = formData.cache_enable_compression === 'on' ? '1' : '0';

        $.post('{{ route("lpadmin.cache.updateSettings") }}', formData, function(res) {
            layer.close(loadIndex);

            if (res.code === 0) {
                layer.msg('设置保存成功', {icon: 1, time: 2000});
            } else {
                layer.msg('保存失败：' + (res.message || '未知错误'), {icon: 2});
            }
        }).fail(function(xhr) {
            layer.close(loadIndex);
            layer.msg('保存失败：网络错误', {icon: 2});
        });

        return false;
    });

    // 测试连接
    $('#test-connection').click(function() {
        const driver = $('[name="cache_driver"]').val();
        const loadIndex = layer.load(2, {content: '测试连接中...'});

        $.post('{{ route("lpadmin.cache.testConnection") }}', { driver: driver }, function(res) {
            layer.close(loadIndex);

            if (res.code === 0) {
                layer.msg('连接测试成功', {icon: 1, time: 2000});
                updateConnectionStatus('success');
            } else {
                layer.msg('连接测试失败：' + (res.message || '未知错误'), {icon: 2});
                updateConnectionStatus('error');
            }
        }).fail(function(xhr) {
            layer.close(loadIndex);
            layer.msg('连接测试失败：网络错误', {icon: 2});
            updateConnectionStatus('error');
        });
    });

    // 重置表单
    $('#reset-form').click(function() {
        layer.confirm('确定要重置所有设置吗？', {icon: 3, title: '确认重置'}, function(index) {
            loadSettings();
            layer.close(index);
            layer.msg('已重置为当前保存的设置', {icon: 1});
        });
    });

    // 更新连接状态显示
    function updateConnectionStatus(status) {
        const $button = $('#test-connection');
        $button.find('.connection-status').remove();

        if (status === 'success') {
            $button.append('<span class="connection-status connection-success">连接正常</span>');
        } else if (status === 'error') {
            $button.append('<span class="connection-status connection-error">连接失败</span>');
        }
    }
});
</script>
</body>
</html>
