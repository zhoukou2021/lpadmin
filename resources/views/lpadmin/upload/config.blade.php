<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>文件上传配置</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body class="pear-container">

    <div class="layui-card">
        <div class="layui-card-header">
            <h2 class="header-title">文件上传配置</h2>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="upload-config-form">
                
                <!-- 基础配置 -->
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>基础配置</legend>
                </fieldset>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">最大文件大小</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <input type="number" name="max_size" placeholder="请输入文件大小" class="layui-input" min="1" max="102400">
                    </div>
                    <div class="layui-form-mid layui-word-aux">KB (1KB - 100MB)</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">存储方式</label>
                    <div class="layui-input-inline" style="width: 200px;">
                        <select name="disk">
                            <option value="public">本地公共存储</option>
                            <option value="local">本地私有存储</option>
                        </select>
                    </div>
                    <div class="layui-form-mid layui-word-aux">选择文件存储位置</div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">存储路径</label>
                    <div class="layui-input-inline" style="width: 300px;">
                        <input type="text" name="path" placeholder="请输入存储路径" class="layui-input">
                    </div>
                    <div class="layui-form-mid layui-word-aux">相对于存储磁盘的路径</div>
                </div>

                <!-- 文件类型配置 -->
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>允许的文件类型</legend>
                </fieldset>

                <div class="layui-form-item">
                    <label class="layui-form-label">图片文件</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_image" placeholder="jpg,jpeg,png,gif,webp,bmp,svg" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">用逗号分隔，如：jpg,png,gif</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">文档文件</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_document" placeholder="pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">用逗号分隔，如：pdf,doc,docx</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">视频文件</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_video" placeholder="mp4,avi,mov,wmv,flv,mkv,webm" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">用逗号分隔，如：mp4,avi,mov</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">音频文件</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_audio" placeholder="mp3,wav,flac,aac,ogg,wma" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">用逗号分隔，如：mp3,wav,flac</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">压缩文件</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_archive" placeholder="zip,rar,7z,tar,gz" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">用逗号分隔，如：zip,rar,7z</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">代码文件</label>
                    <div class="layui-input-block">
                        <input type="text" name="extensions_code" placeholder="js,css,html,php,py,java,cpp,c,json,xml" class="layui-input" style="width: 500px;">
                        <div class="layui-form-mid layui-word-aux">用逗号分隔，如：js,css,html</div>
                    </div>
                </div>

                <!-- 安全配置 -->
                <fieldset class="layui-elem-field layui-field-title">
                    <legend>安全配置</legend>
                </fieldset>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <input type="checkbox" name="enable_security_check" title="启用安全检查" >
                        <div class="layui-form-mid layui-word-aux">检查文件内容是否包含恶意代码</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <input type="checkbox" name="enable_duplicate_check" title="启用重复文件检查" >
                        <div class="layui-form-mid layui-word-aux">通过MD5哈希检查重复文件</div>
                    </div>
                </div>

                <!-- 操作按钮 -->
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="pear-btn pear-btn-primary" lay-submit lay-filter="config-submit">
                            <i class="layui-icon layui-icon-ok"></i>
                            保存配置
                        </button>
                        <button type="reset" class="pear-btn pear-btn-warm">
                            <i class="layui-icon layui-icon-refresh"></i>
                            重置
                        </button>
                        <button type="button" class="pear-btn pear-btn-normal" id="load-current">
                            <i class="layui-icon layui-icon-download-circle"></i>
                            加载当前配置
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        layui.use(['form', 'jquery'], function () {
            let form = layui.form;
            let $ = layui.jquery;

            // 加载当前配置
            function loadCurrentConfig() {
                $.get('{{ route("lpadmin.upload.config") }}', function(res) {
                    if (res.code === 0) {
                        let config = res.data;
                        
                        // 基础配置
                        $('input[name="max_size"]').val(Math.floor(config.max_size / 1024));
                        $('select[name="disk"]').val(config.disk);
                        $('input[name="path"]').val(config.path);
                        
                        // 文件类型配置
                        $('input[name="extensions_image"]').val(config.allowed_extensions.image ? config.allowed_extensions.image.join(',') : '');
                        $('input[name="extensions_document"]').val(config.allowed_extensions.document ? config.allowed_extensions.document.join(',') : '');
                        $('input[name="extensions_video"]').val(config.allowed_extensions.video ? config.allowed_extensions.video.join(',') : '');
                        $('input[name="extensions_audio"]').val(config.allowed_extensions.audio ? config.allowed_extensions.audio.join(',') : '');
                        $('input[name="extensions_archive"]').val(config.allowed_extensions.archive ? config.allowed_extensions.archive.join(',') : '');
                        $('input[name="extensions_code"]').val(config.allowed_extensions.code ? config.allowed_extensions.code.join(',') : '');

                        // 安全配置
                        $('input[name="enable_security_check"]').prop('checked', config.enable_security_check);
                        $('input[name="enable_duplicate_check"]').prop('checked', config.enable_duplicate_check);

                        form.render();
                    } else {
                        layer.msg('加载配置失败', {icon: 2});
                    }
                });
            }

            // 页面加载时自动加载当前配置
            loadCurrentConfig();

            // 手动加载配置按钮
            $('#load-current').click(function() {
                loadCurrentConfig();
                layer.msg('配置已重新加载', {icon: 1, time: 1500});
            });

            // 表单提交
            form.on('submit(config-submit)', function(data) {
                let formData = data.field;
                
                // 处理文件扩展名
                let allowedExtensions = [];
                ['image', 'document', 'video', 'audio', 'archive', 'code'].forEach(function(type) {
                    let extensions = formData['extensions_' + type];
                    if (extensions) {
                        extensions.split(',').forEach(function(ext) {
                            ext = ext.trim();
                            if (ext && allowedExtensions.indexOf(ext) === -1) {
                                allowedExtensions.push(ext);
                            }
                        });
                    }
                });

                let submitData = {
                    max_size: parseInt(formData.max_size),
                    disk: formData.disk,
                    path: formData.path,
                    allowed_extensions: allowedExtensions,
                    _token: '{{ csrf_token() }}'
                };

                // 处理布尔值字段
                if (formData.enable_security_check !== undefined) {
                    submitData.enable_security_check = formData.enable_security_check === 'on' ? 1 : 0;
                }
                if (formData.enable_duplicate_check !== undefined) {
                    submitData.enable_duplicate_check = formData.enable_duplicate_check === 'on' ? 1 : 0;
                }
                if (!formData.enable_security_check) {
                    submitData.enable_security_check = 0;
                }
                if (!formData.enable_duplicate_check) {
                    submitData.enable_duplicate_check = 0;
                }

                $.ajax({
                    url: '{{ route("lpadmin.upload.config_update") }}',
                    type: 'POST',
                    data: submitData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.code === 0) {
                            layer.msg('配置保存成功', {icon: 1, time: 2000});
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    },
                    error: function() {
                        layer.msg('网络错误，请稍后重试', {icon: 2});
                    }
                });

                return false;
            });
        });
    </script>
</body>
</html>
