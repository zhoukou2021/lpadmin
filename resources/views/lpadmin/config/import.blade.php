<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>导入配置</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <style>
        .import-tips {
            background: #f6ffed;
            border: 1px solid #b7eb8f;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .import-tips h4 {
            color: #52c41a;
            margin-bottom: 10px;
        }
        
        .import-tips ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .import-tips li {
            margin-bottom: 5px;
            color: #666;
        }
        
        .upload-area {
            border: 2px dashed #d9d9d9;
            border-radius: 6px;
            background: #fafafa;
            text-align: center;
            padding: 40px 20px;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        
        .upload-area:hover {
            border-color: #1890ff;
        }
        
        .upload-area.dragover {
            border-color: #1890ff;
            background: #f0f8ff;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #d9d9d9;
            margin-bottom: 16px;
        }
        
        .upload-text {
            color: #666;
            font-size: 14px;
        }
        
        .upload-hint {
            color: #999;
            font-size: 12px;
            margin-top: 8px;
        }
        
        .file-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
        }
        
        .file-info h4 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .file-info .file-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .mode-selection {
            margin-bottom: 20px;
        }
        
        .mode-option {
            border: 1px solid #e8e8e8;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .mode-option:hover {
            border-color: #1890ff;
        }
        
        .mode-option.selected {
            border-color: #1890ff;
            background: #f0f8ff;
        }
        
        .mode-option h4 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .mode-option p {
            margin: 0;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body class="pear-container">
    <div class="layui-card">
        <div class="layui-card-header">
            <h2>导入配置</h2>
        </div>
        <div class="layui-card-body">
            <!-- 导入说明 -->
            <div class="import-tips">
                <h4><i class="layui-icon layui-icon-tips"></i> 导入说明</h4>
                <ul>
                    <li>支持导入JSON格式的配置文件</li>
                    <li>文件大小不能超过2MB</li>
                    <li>配置文件必须包含configs字段</li>
                    <li>合并模式：只导入新配置，跳过已存在的配置</li>
                    <li>替换模式：导入新配置，更新已存在的配置</li>
                </ul>
            </div>

            <form class="layui-form" lay-filter="import-form">
                <!-- 文件上传区域 -->
                <div class="layui-form-item">
                    <label class="layui-form-label">选择文件</label>
                    <div class="layui-input-block">
                        <div class="upload-area" id="upload-area">
                            <div class="upload-icon">
                                <i class="layui-icon layui-icon-upload"></i>
                            </div>
                            <div class="upload-text">点击选择文件或拖拽文件到此处</div>
                            <div class="upload-hint">支持JSON格式，文件大小不超过2MB</div>
                        </div>
                        <input type="file" id="file-input" accept=".json" style="display: none;">
                    </div>
                </div>

                <!-- 文件信息 -->
                <div class="file-info" id="file-info">
                    <h4>文件信息</h4>
                    <div class="file-detail">
                        <span>文件名：</span>
                        <span id="file-name">-</span>
                    </div>
                    <div class="file-detail">
                        <span>文件大小：</span>
                        <span id="file-size">-</span>
                    </div>
                    <div class="file-detail">
                        <span>配置数量：</span>
                        <span id="config-count">-</span>
                    </div>
                </div>

                <!-- 导入模式选择 -->
                <div class="layui-form-item mode-selection">
                    <label class="layui-form-label">导入模式</label>
                    <div class="layui-input-block">
                        <div class="mode-option selected" data-mode="merge">
                            <h4><i class="layui-icon layui-icon-add-circle"></i> 合并模式</h4>
                            <p>只导入新配置，跳过已存在的配置项，不会覆盖现有数据</p>
                        </div>
                        <div class="mode-option" data-mode="replace">
                            <h4><i class="layui-icon layui-icon-refresh"></i> 替换模式</h4>
                            <p>导入新配置并更新已存在的配置项，会覆盖现有数据</p>
                        </div>
                        <input type="hidden" name="mode" value="merge">
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit lay-filter="submit" disabled id="submit-btn">
                            <i class="layui-icon layui-icon-upload"></i> 开始导入
                        </button>
                        <button type="button" class="layui-btn layui-btn-primary" onclick="parent.layer.closeAll()">
                            <i class="layui-icon layui-icon-close"></i> 取消
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        layui.use(['form', 'jquery', 'layer', 'upload'], function() {
            const form = layui.form;
            const $ = layui.jquery;
            const layer = layui.layer;
            const upload = layui.upload;

            const IMPORT_API = "{{ route('lpadmin.config.import') }}";

            let selectedFile = null;
            let configData = null;

            // 文件选择
            $('#upload-area, #file-input').click(function() {
                $('#file-input').click();
            });

            $('#file-input').change(function() {
                const file = this.files[0];
                if (file) {
                    handleFile(file);
                }
            });

            // 拖拽上传
            $('#upload-area').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $('#upload-area').on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            $('#upload-area').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    handleFile(files[0]);
                }
            });

            // 处理文件
            function handleFile(file) {
                // 验证文件类型
                if (!file.name.toLowerCase().endsWith('.json')) {
                    layer.msg('请选择JSON格式的文件', {icon: 2});
                    return;
                }

                // 验证文件大小
                if (file.size > 2 * 1024 * 1024) {
                    layer.msg('文件大小不能超过2MB', {icon: 2});
                    return;
                }

                selectedFile = file;

                // 读取文件内容
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        configData = JSON.parse(e.target.result);
                        
                        if (!configData.configs || !Array.isArray(configData.configs)) {
                            layer.msg('配置文件格式错误，缺少configs字段', {icon: 2});
                            return;
                        }

                        // 显示文件信息
                        $('#file-name').text(file.name);
                        $('#file-size').text(formatFileSize(file.size));
                        $('#config-count').text(configData.configs.length + ' 个');
                        $('#file-info').show();
                        $('#submit-btn').prop('disabled', false);

                    } catch (error) {
                        layer.msg('JSON文件格式错误', {icon: 2});
                    }
                };
                reader.readAsText(file);
            }

            // 格式化文件大小
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // 模式选择
            $('.mode-option').click(function() {
                $('.mode-option').removeClass('selected');
                $(this).addClass('selected');
                $('input[name="mode"]').val($(this).data('mode'));
            });

            // 表单提交
            form.on('submit(submit)', function(data) {
                if (!selectedFile) {
                    layer.msg('请选择要导入的文件', {icon: 2});
                    return false;
                }

                const formData = new FormData();
                formData.append('file', selectedFile);
                formData.append('mode', data.field.mode);

                // 显示进度
                const loadIndex = layer.load(2, {content: '正在导入配置...'});

                $.ajax({
                    url: IMPORT_API,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (result) {
                        layer.close(loadIndex);
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
                        layer.close(loadIndex);
                        layer.msg('导入失败', {icon: 2});
                    }
                });

                return false;
            });

        });
    </script>

</body>
</html>
