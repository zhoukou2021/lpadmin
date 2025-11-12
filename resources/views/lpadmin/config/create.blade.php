<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>新增配置</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <style>
        .layui-form-item .layui-form-label.required::before {
            content: "*";
            color: red;
            margin-right: 4px;
        }
        
        .options-config {
            display: none;
            margin-top: 10px;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .option-item input {
            margin-right: 10px;
        }
        
        .option-item .layui-btn {
            margin-left: 10px;
        }
        
        .type-description {
            color: #999;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body class="pear-container">
    <div class="layui-card">
        <div class="layui-card-header">
            <h2>新增配置</h2>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="config-form">
                <div class="layui-row layui-col-space20">
                    <div class="layui-col-md6">
                        <div class="layui-form-item">
                            <label class="layui-form-label required">配置分组</label>
                            <div class="layui-input-block">
                                <select name="group" lay-verify="required" lay-search id="group-select">
                                    <option value="">请选择配置分组</option>
                                </select>
                                <div class="type-description">选择现有分组或在分组管理中创建新分组</div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-md6">
                        <div class="layui-form-item">
                            <label class="layui-form-label required">配置名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="name" placeholder="请输入配置名称" class="layui-input" lay-verify="required" lay-reqtext="配置名称不能为空">
                                <div class="type-description">唯一标识，建议使用英文和下划线</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-row layui-col-space20">
                    <div class="layui-col-md6">
                        <div class="layui-form-item">
                            <label class="layui-form-label required">配置标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="title" placeholder="请输入配置标题" class="layui-input" lay-verify="required" lay-reqtext="配置标题不能为空">
                                <div class="type-description">用于显示的中文名称</div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-md6">
                        <div class="layui-form-item">
                            <label class="layui-form-label required">配置类型</label>
                            <div class="layui-input-block">
                                <select name="type" lay-verify="required" lay-reqtext="请选择配置类型" lay-filter="type-change">
                                    <option value="">请选择配置类型</option>
                                    <option value="text">文本框</option>
                                    <option value="textarea">文本域</option>
                                    <option value="number">数字</option>
                                    <option value="select">下拉选择</option>
                                    <option value="radio">单选框</option>
                                    <option value="checkbox">复选框</option>
                                    <option value="switch">开关</option>
                                    <option value="image">图片</option>
                                    <option value="file">文件</option>
                                    <option value="color">颜色</option>
                                    <option value="date">日期</option>
                                    <option value="datetime">日期时间</option>
                                    <option value="richtext">富文本</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">配置值</label>
                    <div class="layui-input-block">
                        <input type="text" name="value" placeholder="请输入配置值" class="layui-input" id="value-input">
                        <div class="type-description">配置的默认值</div>
                    </div>
                </div>

                <!-- 多语言开关 -->
                <div class="layui-form-item" id="i18n-config" style="display: none;">
                    <label class="layui-form-label">多语言</label>
                    <div class="layui-input-block">
                        <input type="checkbox" name="is_i18n" lay-skin="switch" lay-text="开启|关闭" value="1">
                        <div class="type-description">开启后将支持多语言内容保存</div>
                    </div>
                </div>

                <!-- 选项配置区域 -->
                <div class="layui-form-item options-config" id="options-config">
                    <label class="layui-form-label">选项配置</label>
                    <div class="layui-input-block">
                        <div id="options-container">
                            <!-- 动态生成的选项 -->
                        </div>
                        <button type="button" class="layui-btn layui-btn-sm" id="add-option">
                            <i class="layui-icon layui-icon-add-1"></i> 添加选项
                        </button>
                        <div class="type-description">
                            用于select、radio、checkbox类型的选项配置<br>
                            格式：{"value1":"显示文本1","value2":"显示文本2"}
                        </div>
                    </div>
                </div>

                <div class="layui-row layui-col-space20">
                    <div class="layui-col-md6">
                        <div class="layui-form-item">
                            <label class="layui-form-label">排序</label>
                            <div class="layui-input-block">
                                <input type="number" name="sort" placeholder="请输入排序值" class="layui-input" value="0" min="0">
                                <div class="type-description">数值越大越靠前</div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-col-md6">
                        <div class="layui-form-item">
                            <label class="layui-form-label">描述</label>
                            <div class="layui-input-block">
                                <input type="text" name="description" placeholder="请输入配置描述" class="layui-input">
                                <div class="type-description">配置项的说明信息</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit lay-filter="submit">
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
            </form>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script src="/static/admin/js/radio-fix.js"></script>
    <script>
        layui.use(['form', 'jquery', 'layer'], function() {
            const form = layui.form;
            const $ = layui.jquery;
            const layer = layui.layer;

            const STORE_API = "{{ route('lpadmin.config.store') }}";
            const GROUPS_API = "{{ route('lpadmin.config.groups.index') }}";

            // 需要选项配置的类型
            const optionTypes = ['select', 'radio', 'checkbox'];

            // 加载配置分组
            loadGroups();

            // 监听类型变化
            form.on('select(type-change)', function(data) {
                const type = data.value;
                const optionsConfig = $('#options-config');
                const i18nConfig = $('#i18n-config');
                
                // 显示/隐藏选项配置
                if (optionTypes.includes(type)) {
                    optionsConfig.show();
                    // 如果没有选项，添加默认选项
                    if ($('#options-container .option-item').length === 0) {
                        addOption('', '');
                        addOption('', '');
                    }
                } else {
                    optionsConfig.hide();
                }
                
                // 显示/隐藏多语言开关（text, textarea, richtext 类型显示）
                if (['text', 'textarea', 'richtext'].includes(type)) {
                    i18nConfig.show();
                } else {
                    i18nConfig.hide();
                }
            });

            // 添加选项
            $('#add-option').click(function() {
                addOption('', '');
            });

            // 添加选项函数
            function addOption(value, text) {
                const optionHtml = `
                    <div class="option-item">
                        <input type="text" placeholder="选项值" class="layui-input option-value" style="width: 150px;" value="${value}">
                        <input type="text" placeholder="显示文本" class="layui-input option-text" style="width: 200px;" value="${text}">
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-danger remove-option">
                            <i class="layui-icon layui-icon-delete"></i>
                        </button>
                    </div>
                `;
                $('#options-container').append(optionHtml);
            }

            // 删除选项
            $(document).on('click', '.remove-option', function() {
                $(this).closest('.option-item').remove();
            });

            // 加载配置分组
            function loadGroups() {
                $.get(GROUPS_API, function(res) {
                    if (res.code === 0 && res.data) {
                        const groupSelect = $('#group-select');
                        groupSelect.empty();
                        groupSelect.append('<option value="">请选择配置分组</option>');

                        res.data.forEach(function(group) {
                            groupSelect.append('<option value="' + group.name + '">' + group.title + ' (' + group.name + ')</option>');
                        });

                        form.render('select');
                    }
                });
            }

            // 表单提交
            form.on('submit(submit)', function(data) {
                const field = data.field;
                
                // 处理选项配置
                if (optionTypes.includes(field.type)) {
                    const options = {};
                    $('#options-container .option-item').each(function() {
                        const value = $(this).find('.option-value').val();
                        const text = $(this).find('.option-text').val();
                        if (value && text) {
                            options[value] = text;
                        }
                    });
                    field.options = JSON.stringify(options);
                }

                // 提交数据
                $.ajax({
                    url: STORE_API,
                    method: 'POST',
                    data: field,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (result) {
                        if (result.code === 0) {
                            layer.msg(result.message, {icon: 1}, function() {
                                parent.refreshTable();
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
