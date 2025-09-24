<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>文件选择器</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body>

    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" action="">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <div class="layui-input-inline">
                            <input type="text" name="original_name" placeholder="请输入文件名" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="selector-query">
                            <i class="layui-icon layui-icon-search"></i>
                            查询
                        </button>
                        <button type="button" class="pear-btn pear-btn-md pear-btn-warm" id="upload-new-file">
                            <i class="layui-icon layui-icon-upload"></i>
                            上传新文件
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="layui-card">
        <div class="layui-card-body">
            <div class="file-grid" id="file-grid">
                <!-- 文件网格将在这里动态生成 -->
            </div>
            <div id="file-pagination"></div>
        </div>
    </div>

    <!-- 固定底部按钮 -->
    <div class="fixed-bottom-buttons">
        <div class="button-container">
            <button type="button" class="pear-btn pear-btn-primary" id="confirm-select">
                <i class="layui-icon layui-icon-ok"></i>
                确定选择
            </button>
            <button type="button" class="pear-btn pear-btn-warm" onclick="parent.layer.closeAll()">
                <i class="layui-icon layui-icon-close"></i>
                取消
            </button>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        layui.use(['form', 'laypage', 'jquery', 'upload', 'layer'], function(){
            let form = layui.form;
            let laypage = layui.laypage;
            let upload = layui.upload;
            let layer = layui.layer;
            let $ = layui.jquery;

            let selectedFiles = [];
            let currentPage = 1;
            let pageSize = 20;

            // 获取选择模式（单选或多选）
            let selectMode = new URLSearchParams(window.location.search).get('mode') || 'multiple';
            let isMultiple = selectMode === 'multiple';

            // 加载文件列表
            function loadFiles(page = 1, params = {}) {
                currentPage = page;
                $.ajax({
                    url: '{{ route("lpadmin.upload.selector") }}',
                    type: 'GET',
                    data: Object.assign({
                        page: page,
                        limit: pageSize
                    }, params),
                    success: function(res) {
                        if (res.code === 0) {
                            let files = res.data || [];
                            let total = res.count || 0;
                            renderFiles(files);
                            renderPagination(total, page);
                        } else {
                            console.error('获取文件列表失败:', res.message);
                            renderFiles([]);
                            renderPagination(0, page);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('请求失败:', error);
                        renderFiles([]);
                        renderPagination(0, page);
                    }
                });
            }

            // 渲染文件网格
            function renderFiles(files) {
                let html = '';
                if (!files || !Array.isArray(files)) {
                    $('#file-grid').html('<div style="text-align: center; padding: 50px; color: #999;">暂无文件</div>');
                    return;
                }

                files.forEach(function(file) {
                    let isImage = file.mime_type && file.mime_type.indexOf('image/') === 0;
                    let preview = '';

                    if (isImage) {
                        preview = `<img src="${file.url}" alt="${file.original_name}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px;">`;
                    } else {
                        // 根据文件类型显示不同的图标
                        let iconClass = 'layui-icon-file';
                        let iconColor = '#999';

                        if (file.mime_type) {
                            if (file.mime_type.indexOf('video/') === 0) {
                                iconClass = 'layui-icon-video';
                                iconColor = '#ff5722';
                            } else if (file.mime_type.indexOf('audio/') === 0) {
                                iconClass = 'layui-icon-music';
                                iconColor = '#9c27b0';
                            } else if (file.extension === 'pdf') {
                                iconClass = 'layui-icon-file-b';
                                iconColor = '#f44336';
                            } else if (['doc', 'docx'].includes(file.extension)) {
                                iconClass = 'layui-icon-file-b';
                                iconColor = '#2196f3';
                            } else if (['xls', 'xlsx'].includes(file.extension)) {
                                iconClass = 'layui-icon-file-b';
                                iconColor = '#4caf50';
                            } else if (['zip', 'rar', '7z'].includes(file.extension)) {
                                iconClass = 'layui-icon-file';
                                iconColor = '#ff9800';
                            }
                        }

                        preview = `
                            <div class="file-icon-container">
                                <i class="layui-icon ${iconClass}" style="font-size: 48px; color: ${iconColor};"></i>
                                <div class="file-extension">${file.extension.toUpperCase()}</div>
                            </div>
                        `;
                    }

                    // 文件名截断显示
                    let displayName = file.original_name;
                    if (displayName.length > 20) {
                        displayName = displayName.substring(0, 17) + '...';
                    }

                    html += `
                        <div class="file-item" data-id="${file.id}" data-url="${file.url}" data-name="${file.original_name}">
                            <div class="file-preview">${preview}</div>
                            <div class="file-info">
                                <div class="file-name" title="${file.original_name}">${displayName}</div>
                            </div>
                            <div class="file-select">
                                <input type="checkbox" lay-skin="primary" lay-filter="file-check" value="${file.id}">
                            </div>
                        </div>
                    `;
                });
                $('#file-grid').html(html);
                form.render('checkbox');
            }

            // 渲染分页
            function renderPagination(total, current) {
                laypage.render({
                    elem: 'file-pagination',
                    count: total,
                    curr: current,
                    limit: pageSize,
                    layout: ['count', 'prev', 'page', 'next', 'limit', 'skip'],
                    jump: function(obj, first) {
                        if (!first) {
                            loadFiles(obj.curr);
                        }
                    }
                });
            }

            // 搜索表单提交
            form.on('submit(selector-query)', function(data) {
                loadFiles(1, data.field);
                return false;
            });

            // 文件选择 - 使用事件委托
            $(document).on('change', 'input[lay-filter="file-check"]', function() {
                let checkbox = $(this);
                let fileItem = checkbox.closest('.file-item');
                let fileData = {
                    id: fileItem.data('id'),
                    url: fileItem.data('url'),
                    name: fileItem.data('name')
                };

                if (checkbox.is(':checked')) {
                    // 单选模式：清除其他选择
                    if (!isMultiple) {
                        // 取消其他所有选择
                        $('input[lay-filter="file-check"]').not(checkbox).prop('checked', false);
                        $('.file-item').removeClass('selected');
                        selectedFiles = [];
                    }

                    fileItem.addClass('selected');
                    // 检查是否已存在，避免重复添加
                    if (!selectedFiles.find(f => f.id === fileData.id)) {
                        selectedFiles.push(fileData);
                    }
                } else {
                    fileItem.removeClass('selected');
                    selectedFiles = selectedFiles.filter(f => f.id !== fileData.id);
                }

                // 更新选择计数显示
                updateSelectedCount();
            });

            // 更新选择计数
            function updateSelectedCount() {
                let count = selectedFiles.length;
                let text;
                if (isMultiple) {
                    text = count > 0 ? `确定选择 (${count})` : '确定选择';
                } else {
                    text = count > 0 ? '确定选择' : '确定选择';
                }
                $('#confirm-select').text(text);
            }

            // 确定选择
            $('#confirm-select').click(function() {
                if (selectedFiles.length === 0) {
                    layer.msg('请选择文件');
                    return;
                }

                // 获取URL参数中的回调函数名
                let callback = new URLSearchParams(window.location.search).get('callback');
                if (callback && parent[callback]) {
                    parent[callback](selectedFiles);
                } else {
                    // 默认回调
                    if (parent.selectFileCallback) {
                        parent.selectFileCallback(selectedFiles);
                    }
                }
                parent.layer.closeAll();
            });

            // 上传新文件功能
            upload.render({
                elem: '#upload-new-file',
                url: '{{ route("lpadmin.upload.file") }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                accept: '{{ request("type") == "image" ? "images" : "file" }}',
                acceptMime: '{{ request("type") == "image" ? "image/*" : "*/*" }}',
                multiple: true,
                done: function(res) {
                    if (res.code === 0) {
                        layer.msg('上传成功', {icon: 1});
                        // 重新加载文件列表
                        loadFiles(currentPage, {
                            type: '{{ request("type", "") }}'
                        });
                    } else {
                        layer.msg(res.message || '上传失败', {icon: 2});
                    }
                },
                error: function() {
                    layer.msg('上传失败，请重试', {icon: 2});
                }
            });

            // 初始加载
            loadFiles(1, {
                type: '{{ request("type", "") }}'
            });
        });
    </script>

    <style>
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .file-item {
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }
        .file-item:hover {
            border-color: #1E9FFF;
            box-shadow: 0 2px 8px rgba(30, 159, 255, 0.2);
        }
        .file-item.selected {
            border-color: #1E9FFF;
            background-color: #f0f9ff;
        }
        .file-preview {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 6px;
            overflow: hidden;
        }
        .file-icon-container {
            text-align: center;
            padding: 10px;
        }
        .file-extension {
            font-size: 12px;
            font-weight: bold;
            color: #666;
            margin-top: 5px;
        }
        .file-info {
            margin-bottom: 8px;
        }
        .file-name {
            font-size: 12px;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }
        .file-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .file-size {
            font-size: 11px;
            color: #666;
        }
        .file-category {
            font-size: 10px;
            color: #1890ff;
            background: #e6f7ff;
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
            max-width: fit-content;
        }
        .file-select {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        #file-pagination {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 0px; /* 为固定底部按钮留出空间 */
        }

        /* 固定底部按钮样式 */
        .fixed-bottom-buttons {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e6e6e6;
            padding: 15px 20px;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            height: 30px;
        }

        .button-container {
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .button-container .pear-btn {
            margin: 0 10px;
            min-width: 120px;
        }

        /* 为页面内容添加底部边距 */
        body {
            padding-bottom: 80px;
        }

        .layui-card {
            margin-bottom: 20px;
        }
    </style>
</body>
</html>
