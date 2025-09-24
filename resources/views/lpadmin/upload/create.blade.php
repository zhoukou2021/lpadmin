<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>上传文件</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/form-common.css" />
</head>
<body>

    <form class="layui-form" action="">
        <div class="mainBox">
            <div class="main-container mr-5">

                {{-- 使用通用单选框组件 - 文件分类 --}}
                @include('lpadmin.components.radio-group', [
                    'name' => 'category',
                    'label' => '文件分类',
                    'required' => false,
                    'options' => [
                        ['value' => '', 'title' => '自动检测'],
                        ['value' => 'general', 'title' => '通用文件'],
                        ['value' => 'avatar', 'title' => '头像图片'],
                        ['value' => 'document', 'title' => '文档资料'],
                        ['value' => 'image', 'title' => '图片素材'],
                        ['value' => 'video', 'title' => '视频文件'],
                        ['value' => 'audio', 'title' => '音频文件'],
                        ['value' => 'archive', 'title' => '压缩包']
                    ],
                    'default' => '',
                    'class' => 'upload-category'
                ])

                <div class="layui-form-item">
                    <label class="layui-form-label">文件标签</label>
                    <div class="layui-input-block">
                        <input type="text" name="tags" placeholder="请输入标签，多个标签用逗号分隔" class="layui-input">
                        <div class="layui-form-mid layui-word-aux">例如：重要,工作,设计</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">文件上传</label>
                    <div class="layui-input-block">
                        <div class="layui-upload">
                            <button type="button" class="layui-btn" id="upload-btn">选择文件</button>
                            <div class="layui-upload-list">
                                <table class="layui-table">
                                    <thead>
                                        <tr>
                                            <th>文件名</th>
                                            <th>大小</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="upload-list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <div class="upload-tips">
                            <p><strong>上传说明：</strong></p>
                            <ul>
                                <li>支持的图片格式：jpg、jpeg、png、gif、webp</li>
                                <li>支持的文档格式：pdf、doc、docx、xls、xlsx、ppt、pptx</li>
                                <li>支持的视频格式：mp4、avi、mov、wmv</li>
                                <li>支持的音频格式：mp3、wav、flac</li>
                                <li>支持的压缩格式：zip、rar、7z</li>
                                <li>单个文件最大：10MB</li>
                                <li>可同时上传多个文件</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="bottom-btn">
            <div class="button-container">
                <button type="button" class="pear-btn pear-btn-primary" id="upload-all">
                    <i class="layui-icon layui-icon-upload"></i>
                    开始上传
                </button>
                <button type="button" class="pear-btn pear-btn-warm" onclick="parent.layer.closeAll()">
                    <i class="layui-icon layui-icon-close"></i>
                    关闭
                </button>
            </div>
        </div>
    </form>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script src="/static/admin/js/radio-fix.js"></script>
    <script>
        layui.use(['upload', 'element', 'layer', 'form'], function(){
            let upload = layui.upload;
            let element = layui.element;
            let form = layui.form;
            let $ = layui.jquery;

            // 初始化单选框
            if (window.RadioHelper) {
                RadioHelper.init('category');
            }

            // 渲染表单
            form.render();

            // 多文件列表示例
            let uploadListIns = upload.render({
                elem: '#upload-btn',
                url: '{{ route("lpadmin.upload.file") }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                accept: 'file',
                multiple: true,
                auto: false,
                bindAction: '#upload-all',
                size: 10240, // 10MB限制
                data: function(){
                    return {
                        'category': $('input[name="category"]:checked').val(),
                        'tags': $('input[name="tags"]').val()
                    };
                },
                // 文件选择前的验证
                before: function(obj){
                    layer.load(1, {shade: [0.1,'#fff']});
                },
                choose: function(obj){
                    let files = this.files = obj.pushFile(); // 将每次选择的文件追加到文件队列
                    
                    // 读取本地文件
                    obj.preview(function(index, file, result){
                        let tr = $(['<tr id="upload-'+ index +'">',
                            '<td>'+ file.name +'</td>',
                            '<td>'+ (file.size/1014).toFixed(1) +'kb</td>',
                            '<td>等待上传</td>',
                            '<td>',
                                '<button class="layui-btn layui-btn-xs upload-reload layui-hide">重传</button>',
                                '<button class="layui-btn layui-btn-xs layui-btn-danger upload-delete">删除</button>',
                            '</td>',
                        '</tr>'].join(''));
                        
                        // 单个重传
                        tr.find('.upload-reload').on('click', function(){
                            obj.upload(index, file);
                        });
                        
                        // 删除
                        tr.find('.upload-delete').on('click', function(){
                            delete files[index]; // 删除对应的文件
                            tr.remove();
                            uploadListIns.config.elem.next()[0].value = ''; // 清空 input file 值，以免删除后出现同名文件不可选
                        });
                        
                        $('#upload-list').append(tr);
                    });
                },
                done: function(res, index, upload){
                    // 关闭加载框
                    layer.closeAll('loading');

                    if(res.code == 0 || res.code == 200){ // 上传成功
                        let tr = $('#upload-list tr#upload-'+ index);
                        let tds = tr.children();
                        tds.eq(2).html('<span style="color: #5FB878;">上传成功</span>');
                        tds.eq(3).html(''); // 清空操作
                        return delete this.files[index]; // 删除文件队列已经上传成功的文件
                    }
                    this.error(index, upload, res);
                },
                allDone: function(obj){
                    // 确保关闭所有加载框
                    layer.closeAll('loading');
                    layer.msg('全部文件上传完成', {icon: 1});

                    // 刷新父页面表格
                    if (parent.layui && parent.layui.table) {
                        parent.layui.table.reload('upload-table');
                    }
                },
                error: function(index, upload, res){
                    // 关闭加载框
                    layer.closeAll('loading');

                    let tr = $('#upload-list tr#upload-'+ index);
                    let tds = tr.children();

                    // 获取错误信息
                    let errorMsg = '上传失败';
                    if (res && res.message) {
                        errorMsg = res.message;
                    } else if (res && res.msg) {
                        errorMsg = res.msg;
                    } else if (upload && upload.response) {
                        try {
                            let response = JSON.parse(upload.response);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            errorMsg = '上传失败：服务器响应异常';
                        }
                    }

                    // 显示错误状态
                    tds.eq(2).html('<span style="color: #FF5722;">'+errorMsg+'</span>');
                    tds.eq(3).find('.upload-reload').removeClass('layui-hide'); // 显示重传
                }
            });
        });
    </script>

    <style>
        .upload-tips {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin-top: 10px;
        }
        .upload-tips p {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .upload-tips ul {
            margin: 0;
            padding-left: 20px;
        }
        .upload-tips li {
            margin: 5px 0;
            color: #6c757d;
            font-size: 13px;
        }
        .layui-upload-list {
            margin-top: 10px;
        }
        .layui-upload-list .layui-table {
            margin: 0;
        }
        .layui-upload-list .layui-table th,
        .layui-upload-list .layui-table td {
            padding: 8px 15px;
            text-align: center;
        }
        .layui-upload-list .layui-table th:first-child,
        .layui-upload-list .layui-table td:first-child {
            text-align: left;
        }

        /* 按钮布局修复 */
        .bottom-btn {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #e6e6e6;
            padding: 15px 20px;
            text-align: center;
            z-index: 9999;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
            height: 30px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }

        .button-container .pear-btn {
            min-width: 120px;
            height: 40px;
            line-height: 40px;
            padding: 0 20px;
        }

        /* 为底部按钮留出更多空间 */
        .mainBox {
            padding-bottom: 100px;
            min-height: calc(100vh - 100px);
        }

        /* 确保页面内容不会被按钮遮挡 */
        body {
            padding-bottom: 80px;
        }

        /* 单选框布局优化 */
        .layui-form-item .layui-input-block .layui-form-radio {
            margin-right: 15px;
            margin-bottom: 8px;
        }

        /* 分类单选框换行显示 */
        .layui-form-item[data-category] .layui-input-block {
            line-height: 32px;
        }

        .layui-form-item[data-category] .layui-form-radio {
            display: inline-block;
            width: auto;
            margin-right: 20px;
            margin-bottom: 10px;
        }
    </style>
</body>
</html>
