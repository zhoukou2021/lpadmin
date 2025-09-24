<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>新增字典</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body>
    <form class="layui-form" action="">
        <div class="mainBox">
            <div class="main-container mr-5">
                <div class="layui-form-item">
                    <label class="layui-form-label required">字典名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" placeholder="请输入字典名称（唯一标识）"
                               class="layui-input" lay-verify="required" lay-reqtext="字典名称不能为空">
                        <div class="layui-form-mid layui-word-aux">
                            用于程序调用的唯一标识，建议使用英文字母、数字和下划线
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">字典标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" placeholder="请输入字典标题"
                               class="layui-input" lay-verify="required" lay-reqtext="字典标题不能为空">
                        <div class="layui-form-mid layui-word-aux">
                            用于显示的中文标题
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">字典类型</label>
                    <div class="layui-input-block">
                        <select name="type" lay-verify="required" lay-reqtext="请选择字典类型">
                            <option value="">请选择字典类型</option>
                            <option value="select">下拉选择</option>
                                            <option value="radio">单选框</option>
                            <option value="checkbox">复选框</option>
                        </select>
                        <div class="layui-form-mid layui-word-aux">
                            选择字典的展示类型
                        </div>
                    </div>
                </div>

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

                <div class="layui-form-item">
                    <label class="layui-form-label">字典描述</label>
                    <div class="layui-input-block">
                        <textarea name="description" placeholder="请输入字典描述"
                                  class="layui-textarea" rows="3"></textarea>
                        <div class="layui-form-mid layui-word-aux">
                            对字典用途的详细说明
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label required">状态</label>
                    <div class="layui-input-block">
                        <input type="radio" name="status" data-value="1" title="启用" lay-filter="status" checked>
                        <input type="radio" name="status" data-value="0" title="禁用" lay-filter="status">
                        <div class="layui-form-mid layui-word-aux">
                            禁用后字典将不可用
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
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
                </div>
            </div>
        </div>
    </form>

    <script src="/static/admin/component/layui/layui.js"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        // 相关常量
        const SAVE_API = "{{ route('lpadmin.dictionary.store') }}";

        layui.use(['form', 'layer'], function(){
            var form = layui.form;
            var layer = layui.layer;
            var $ = layui.$;

            // 自动生成字典名称
            $('input[name="title"]').on('blur', function(){
                var title = $(this).val();
                var nameInput = $('input[name="name"]');
                if(title && !nameInput.val()){
                    // 简单的拼音转换（这里只是示例，实际项目中可能需要更完善的转换）
                    var name = title.toLowerCase()
                        .replace(/[\u4e00-\u9fa5]/g, '') // 移除中文字符
                        .replace(/[^a-z0-9]/g, '_') // 非字母数字替换为下划线
                        .replace(/_+/g, '_') // 多个下划线合并为一个
                        .replace(/^_|_$/g, ''); // 移除首尾下划线

                    if(!name){
                        name = 'dict_' + Date.now(); // 如果无法生成，使用时间戳
                    }
                    nameInput.val(name);
                }
            });

            // 表单提交
            form.on('submit(submit)', function(data){
                var loadIndex = layer.load(2, {shade: [0.3, '#fff']});

                // 处理单选框数据
                var formData = data.field;
                formData.status = $('input[name="status"]:checked').attr('data-value');
                formData._token = $('meta[name="csrf-token"]').attr('content');

                $.post(SAVE_API, formData, function(res){
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
                }).fail(function(){
                    layer.close(loadIndex);
                    layer.msg('网络错误，请稍后重试', {icon: 2});
                });

                return false;
            });

            // 字典名称输入提示
            $('input[name="name"]').on('input', function(){
                var value = $(this).val();
                // 自动转换为小写并替换特殊字符
                value = value.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                $(this).val(value);
            });
        });
    </script>
</body>
</html>
