<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>新增字典项 - {{ $dictionary->title }}</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body class="pear-container">
<div class="layui-fluid" style="padding: 15px;">
    <form class="layui-form" lay-filter="itemForm">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">
                        <span class="layui-icon layui-icon-add-1"></span>
                        新增字典项 - {{ $dictionary->title }}
                    </div>
                    <div class="layui-card-body">
                        <div class="layui-row layui-col-space15">
                            <div class="layui-col-md6">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">显示标签 <span style="color: red;">*</span></label>
                                    <div class="layui-input-block">
                                        <input type="text" name="label" placeholder="请输入显示标签" 
                                               class="layui-input" lay-verify="required" lay-reqtext="显示标签不能为空">
                                        <div class="layui-form-mid layui-word-aux">
                                            用户看到的显示文本
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-col-md6">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">选项值 <span style="color: red;">*</span></label>
                                    <div class="layui-input-block">
                                        <input type="text" name="value" placeholder="请输入选项值" 
                                               class="layui-input" lay-verify="required" lay-reqtext="选项值不能为空">
                                        <div class="layui-form-mid layui-word-aux">
                                            程序使用的实际值，同一字典下不能重复
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="layui-row layui-col-space15">
                            <div class="layui-col-md6">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">颜色标识</label>
                                    <div class="layui-input-block">
                                        <select name="color">
                                            <option value="">请选择颜色</option>
                                            <option value="blue">蓝色</option>
                                            <option value="green">绿色</option>
                                            <option value="orange">橙色</option>
                                            <option value="red">红色</option>
                                            <option value="purple">紫色</option>
                                            <option value="cyan">青色</option>
                                            <option value="gray">灰色</option>
                                            <option value="pink">粉色</option>
                                        </select>
                                        <div class="layui-form-mid layui-word-aux">
                                            用于区分不同选项的颜色标识
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-col-md6">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">排序权重</label>
                                    <div class="layui-input-block">
                                        <input type="number" name="sort" value="0" placeholder="请输入排序权重" 
                                               class="layui-input" lay-verify="number">
                                        <div class="layui-form-mid layui-word-aux">
                                            数值越大排序越靠前
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">选项描述</label>
                            <div class="layui-input-block">
                                <textarea name="description" placeholder="请输入选项描述" 
                                          class="layui-textarea" rows="3"></textarea>
                                <div class="layui-form-mid layui-word-aux">
                                    对选项的详细说明
                                </div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">状态 <span style="color: red;">*</span></label>
                            <div class="layui-input-block">
                                <input type="radio" name="status" value="1" title="启用" checked>
                                <input type="radio" name="status" value="0" title="禁用">
                                <div class="layui-form-mid layui-word-aux">
                                    禁用后选项将不可用
                                </div>
                            </div>
                        </div>

                        <!-- 颜色预览 -->
                        <div class="layui-form-item" id="colorPreview" style="display: none;">
                            <label class="layui-form-label">颜色预览</label>
                            <div class="layui-input-block">
                                <span class="layui-badge" id="colorBadge">预览</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-form-item" style="text-align: center; margin-top: 20px;">
            <button class="layui-btn" lay-submit lay-filter="submit">
                <i class="layui-icon layui-icon-ok"></i> 保存
            </button>
            <button type="reset" class="layui-btn layui-btn-primary">
                <i class="layui-icon layui-icon-refresh"></i> 重置
            </button>
            <button type="button" class="layui-btn layui-btn-primary" onclick="parent.layer.closeAll()">
                <i class="layui-icon layui-icon-close"></i> 取消
            </button>
        </div>
    </form>
</div>
    <script src="/static/admin/component/layui/layui.js"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
    layui.use(['form', 'layer'], function(){
        var form = layui.form;
        var layer = layui.layer;
        var $ = layui.$;

        // 表单提交
        form.on('submit(submit)', function(data){
            var loadIndex = layer.load(2, {shade: [0.3, '#fff']});

            $.ajax({
                url: '{{ route("lpadmin.dictionary.items.store", $dictionary->id) }}',
                type: 'POST',
                data: data.field,
                success: function(res){
                    layer.close(loadIndex);

                    if(res.code === 0){
                        layer.msg(res.message, {icon: 1, time: 1500}, function(){
                            parent.layer.closeAll();
                        });
                    } else {
                        layer.msg(res.message, {icon: 2});

                        // 显示验证错误
                        if(res.data && typeof res.data === 'object'){
                            var errors = [];
                            for(var field in res.data){
                                if(res.data[field] instanceof Array){
                                    errors = errors.concat(res.data[field]);
                                }
                            }
                            if(errors.length > 0){
                                layer.msg(errors.join('<br>'), {icon: 2});
                            }
                        }
                    }
                },
                error: function(){
                    layer.close(loadIndex);
                    layer.msg('网络错误，请稍后重试', {icon: 2});
                }
            });

            return false;
        });

        // 颜色选择监听
        form.on('select()', function(data){
            if(data.elem.name === 'color'){
                updateColorPreview(data.value);
            }
        });

        // 更新颜色预览
        function updateColorPreview(color) {
            var colorPreview = $('#colorPreview');
            var colorBadge = $('#colorBadge');

            if(color) {
                colorPreview.show();
                colorBadge.removeClass().addClass('layui-badge layui-bg-' + color).text(color);
            } else {
                colorPreview.hide();
            }
        }

        // 根据标签自动生成值
        $('input[name="label"]').on('blur', function(){
            var label = $(this).val();
            var valueInput = $('input[name="value"]');

            if(label && !valueInput.val()){
                // 简单的转换规则
                var value = label.toLowerCase().replace(/[\s\u4e00-\u9fa5]/g, '');
                if(!value) {
                    value = 'option_' + Date.now();
                }
                valueInput.val(value);
            }
        });

        // 选项值输入规范化
        $('input[name="value"]').on('input', function(){
            var value = $(this).val();
            // 移除特殊字符，保留字母、数字、下划线、连字符
            value = value.replace(/[^a-zA-Z0-9_-]/g, '');
            $(this).val(value);
        });
    });
    </script>
</body>
</html>
