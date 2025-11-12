<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>系统配置管理</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    <style>
        .config-type-tag {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            color: #fff;
        }
        .type-text { background-color: #1890ff; }
        .type-textarea { background-color: #52c41a; }
        .type-number { background-color: #fa8c16; }
        .type-select { background-color: #722ed1; }
        .type-radio { background-color: #eb2f96; }
        .type-checkbox { background-color: #13c2c2; }
        .type-switch { background-color: #f5222d; }
        .type-image { background-color: #fa541c; }
        .type-file { background-color: #2f54eb; }
        .type-color { background-color: #faad14; }
        .type-date { background-color: #a0d911; }
        .type-datetime { background-color: #096dd9; }
        .type-richtext { background-color: #722ed1; }
        
        .config-value {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .config-group-tag {
            padding: 2px 6px;
            background-color: #f0f0f0;
            border-radius: 2px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body class="pear-container">
    <!-- 统计信息卡片 -->
    <div class="layui-row layui-col-space15" style="margin-bottom: 15px;">
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="layui-row">
                        <div class="layui-col-xs8">
                            <div style="color: #666; font-size: 14px;">总配置数</div>
                            <div style="font-size: 24px; font-weight: bold; color: #1890ff;" id="total-configs">-</div>
                        </div>
                        <div class="layui-col-xs4" style="text-align: right;">
                            <i class="layui-icon layui-icon-set" style="font-size: 40px; color: #1890ff;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="layui-row">
                        <div class="layui-col-xs8">
                            <div style="color: #666; font-size: 14px;">配置分组</div>
                            <div style="font-size: 24px; font-weight: bold; color: #52c41a;" id="total-groups">-</div>
                        </div>
                        <div class="layui-col-xs4" style="text-align: right;">
                            <i class="layui-icon layui-icon-group" style="font-size: 40px; color: #52c41a;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="layui-row">
                        <div class="layui-col-xs8">
                            <div style="color: #666; font-size: 14px;">系统配置</div>
                            <div style="font-size: 24px; font-weight: bold; color: #13c2c2;" id="system-configs">-</div>
                        </div>
                        <div class="layui-col-xs4" style="text-align: right;">
                            <i class="layui-icon layui-icon-engine" style="font-size: 40px; color: #13c2c2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-body">
                    <div class="layui-row">
                        <div class="layui-col-xs8">
                            <div style="color: #666; font-size: 14px;">自定义配置</div>
                            <div style="font-size: 24px; font-weight: bold; color: #fa8c16;" id="custom-configs">-</div>
                        </div>
                        <div class="layui-col-xs4" style="text-align: right;">
                            <i class="layui-icon layui-icon-util" style="font-size: 40px; color: #fa8c16;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 搜索表单 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form" action="">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">配置分组</label>
                        <div class="layui-input-inline">
                            <select name="group" lay-search>
                                <option value="">全部分组</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">配置类型</label>
                        <div class="layui-input-inline">
                            <select name="type">
                                <option value="">全部类型</option>
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
                    <div class="layui-inline">
                        <label class="layui-form-label">配置名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" placeholder="请输入配置名称" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">配置标题</label>
                        <div class="layui-input-inline">
                            <input type="text" name="title" placeholder="请输入配置标题" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="config-query">
                            <i class="layui-icon layui-icon-search"></i>
                            查询
                        </button>
                        <button type="reset" class="pear-btn pear-btn-md">
                            <i class="layui-icon layui-icon-refresh"></i>
                            重置
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <table id="config-table" lay-filter="config-table"></table>
        </div>
    </div>

    <script type="text/html" id="config-toolbar">
        <div class="layui-btn-group">
            <button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="add">
                <i class="layui-icon layui-icon-add-1"></i>
                新增
            </button>
            <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="batchRemove">
                <i class="layui-icon layui-icon-delete"></i>
                删除
            </button>
            <button class="pear-btn pear-btn-normal pear-btn-sm" lay-event="export">
                <i class="layui-icon layui-icon-export"></i>
                导出
            </button>
            <button class="pear-btn pear-btn-warm pear-btn-sm" lay-event="import">
                <i class="layui-icon layui-icon-upload"></i>
                导入
            </button>
        </div>
    </script>

    <script type="text/html" id="config-toolbar-right">
        <div style="white-space: nowrap; display: flex; gap: 3px; justify-content: center;">
            <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                <i class="layui-icon layui-icon-edit"></i>
            </button>
            <button class="table-action-btn table-action-delete" lay-event="remove" title="删除">
                <i class="layui-icon layui-icon-delete"></i>
            </button>
        </div>
    </script>

    <!-- 配置类型模板 -->
    <script type="text/html" id="type-tpl">
        <span class="config-type-tag type-@{{ d.type }}">@{{ d.type }}</span>
    </script>

    <!-- 配置分组模板 -->
    <script type="text/html" id="group-tpl">
        <span class="config-group-tag">@{{ d.group }}</span>
    </script>

    <!-- 配置值模板 -->
    <script type="text/html" id="value-tpl">
        @{{#
            var value = d.value || '-';
            var displayValue = value.length > 30 ? value.substring(0, 30) + '...' : value;
        }}
        <div class="config-value" title="@{{ value }}">@{{ displayValue }}</div>
    </script>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script src="/static/admin/js/radio-fix.js"></script>
    <script>
        // 相关常量
        const PRIMARY_KEY = "id";
        const SELECT_API = "{{ route('lpadmin.config.select') }}";
        const REMOVE_API = "{{ route('lpadmin.config.destroy', ':id') }}";
        const BATCH_REMOVE_API = "{{ route('lpadmin.config.batchDestroy') }}";
        const ADD_URL = "{{ route('lpadmin.config.create') }}";
        const EDIT_URL = "{{ route('lpadmin.config.edit', ':id') }}";
        const EXPORT_API = "{{ route('lpadmin.config.export') }}";
        const IMPORT_URL = "{{ route('lpadmin.config.importPage') }}";
        const GROUPS_API = "{{ route('lpadmin.config.groups.index') }}";

        layui.use(['table', 'form', 'jquery', 'popup'], function () {
            let table = layui.table;
            let form = layui.form;
            let $ = layui.jquery;

            let cols = [
                [
                    {type: 'checkbox'},
                    {title: 'ID', field: 'id', width: 80, align: 'center'},
                    {title: '分组', field: 'group', width: 120, align: 'center', templet: '#group-tpl'},
                    {title: '配置名称', field: 'name', width: 180},
                    {title: '配置标题', field: 'title', width: 150},
                    {title: '配置值', field: 'value', width: 200, templet: '#value-tpl'},
                    {title: '类型', field: 'type', width: 100, align: 'center', templet: '#type-tpl'},
                    {title: '排序', field: 'sort', width: 80, align: 'center'},
                    {title: '描述', field: 'description', width: 200},
                    {title: '创建时间', field: 'created_at', width: 160, align: 'center'},
                    {title: '操作', width: 100, align: 'center', toolbar: '#config-toolbar-right', fixed: 'right'}
                ]
            ];

            table.render({
                elem: '#config-table',
                url: SELECT_API,
                method: 'GET',
                toolbar: '#config-toolbar',
                defaultToolbar: [{
                    title: '刷新',
                    layEvent: 'refresh',
                    icon: 'layui-icon-refresh',
                }, 'filter', 'print', 'exports'],
                cols: cols,
                skin: 'line',
                size: 'lg',
                page: true,
                limits: [10, 20, 30, 50, 100],
                limit: 10,
                parseData: function(res) {
                    return {
                        code: res.code,
                        msg: res.message,
                        count: res.count,
                        data: res.data
                    };
                },
                done: function(res) {
                    // 更新统计信息
                    updateStatistics();
                }
            });

            // 加载配置分组
            loadGroups();

            // 加载配置分组到下拉选项
            function loadGroups() {
                $.get(GROUPS_API, function(res) {
                    if (res.code === 0 && res.data) {
                        const groupSelect = $('select[name="group"]');
                        groupSelect.empty();
                        groupSelect.append('<option value="">全部分组</option>');

                        // 处理分组数据，可能是数组或对象
                        if (Array.isArray(res.data)) {
                            res.data.forEach(function(group) {
                                if (typeof group === 'object' && group.name) {
                                    groupSelect.append('<option value="' + group.name + '">' + (group.title || group.name) + '</option>');
                                } else {
                                    groupSelect.append('<option value="' + group + '">' + group + '</option>');
                                }
                            });
                        }

                        form.render('select');
                    }
                });
            }

            // 更新统计信息
            function updateStatistics() {
                $.get(SELECT_API + '?limit=1000', function(res) {
                    if (res.code === 0 && res.data) {
                        const configs = res.data || [];
                        const groups = [...new Set(configs.map(item => item.group))];
                        const systemConfigs = configs.filter(item => item.group === 'system');
                        const customConfigs = configs.filter(item => item.group !== 'system');

                        $('#total-configs').text(configs.length);
                        $('#total-groups').text(groups.length);
                        $('#system-configs').text(systemConfigs.length);
                        $('#custom-configs').text(customConfigs.length);
                    }
                });
            }



            // 搜索
            form.on('submit(config-query)', function (data) {
                table.reload('config-table', {
                    where: data.field,
                    page: {curr: 1}
                });
                return false;
            });

            // 工具栏事件
            table.on('toolbar(config-table)', function (obj) {
                if (obj.event === 'add') {
                    layer.open({
                        type: 2,
                        title: '新增配置',
                        shade: 0.1,
                        area: ['800px', '600px'],
                        content: ADD_URL
                    });
                } else if (obj.event === 'batchRemove') {
                    const checkStatus = table.checkStatus('config-table');
                    if (checkStatus.data.length === 0) {
                        layer.msg('请选择要删除的数据', {icon: 2});
                        return;
                    }

                    layer.confirm('确定要删除选中的 ' + checkStatus.data.length + ' 条配置吗？', {
                        icon: 3,
                        title: '提示'
                    }, function(index) {
                        const ids = checkStatus.data.map(item => item[PRIMARY_KEY]);

                        $.ajax({
                            url: BATCH_REMOVE_API,
                            method: 'DELETE',
                            data: {ids: ids},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (result) {
                                if (result.code === 0) {
                                    layer.msg(result.message, {icon: 1});
                                    table.reload('config-table');
                                    updateStatistics();
                                } else {
                                    layer.msg(result.message, {icon: 2});
                                }
                            },
                            error: function () {
                                layer.msg('删除失败', {icon: 2});
                            }
                        });

                        layer.close(index);
                    });
                } else if (obj.event === 'export') {
                    // 导出配置
                    const searchData = form.val('search');
                    let exportUrl = EXPORT_API;
                    if (searchData.group) {
                        exportUrl += '?group=' + searchData.group;
                    }

                    $.get(exportUrl, function(res) {
                        if (res.code === 0) {
                            const dataStr = JSON.stringify(res.data, null, 2);
                            const blob = new Blob([dataStr], {type: 'application/json'});
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'config_export_' + new Date().getTime() + '.json';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                            layer.msg('导出成功', {icon: 1});
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    });
                } else if (obj.event === 'import') {
                    layer.open({
                        type: 2,
                        title: '导入配置',
                        shade: 0.1,
                        area: ['600px', '400px'],
                        content: IMPORT_URL
                    });
                } else if (obj.event === 'clearCache') {
                    layer.confirm('确定要清除配置缓存吗？', {
                        icon: 3,
                        title: '提示'
                    }, function(index) {
                        $.post(CLEAR_CACHE_API, {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }, function(res) {
                            if (res.code === 0) {
                                layer.msg(res.message, {icon: 1});
                            } else {
                                layer.msg(res.message, {icon: 2});
                            }
                        });
                        layer.close(index);
                    });
                } else if (obj.event === 'systemSettings') {
                    layer.open({
                        type: 2,
                        title: '系统设置',
                        shade: 0.1,
                        area: ['1000px', '700px'],
                        content: SYSTEM_SETTINGS_URL
                    });
                } else if (obj.event === 'groupManage') {
                    layer.open({
                        type: 2,
                        title: '分组管理',
                        shade: 0.1,
                        area: ['900px', '600px'],
                        content: "{{ route('lpadmin.config.groups.page') }}"
                    });
                } else if (obj.event === 'refresh') {
                    table.reload("#config-table");
                    updateStatistics();
                }
            });

            // 行工具事件
            table.on('tool(config-table)', function (obj) {
                const data = obj.data;

                if (obj.event === 'edit') {
                    const editUrl = EDIT_URL.replace(':id', data[PRIMARY_KEY]);
                    layer.open({
                        type: 2,
                        title: '编辑配置',
                        shade: 0.1,
                        area: ['800px', '600px'],
                        content: editUrl
                    });
                } else if (obj.event === 'remove') {
                    layer.confirm('确定要删除这个配置吗？', {
                        icon: 3,
                        title: '提示'
                    }, function(index) {
                        const removeUrl = REMOVE_API.replace(':id', data[PRIMARY_KEY]);

                        $.ajax({
                            url: removeUrl,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (result) {
                                if (result.code === 0) {
                                    layer.msg(result.message, {icon: 1});
                                    obj.del();
                                    updateStatistics();
                                } else {
                                    layer.msg(result.message, {icon: 2});
                                }
                            },
                            error: function () {
                                layer.msg('删除失败', {icon: 2});
                            }
                        });

                        layer.close(index);
                    });
                }
            });

            // 全局刷新表格函数
            window.refreshTable = function () {
                table.reload('config-table');
                updateStatistics();
                loadGroups();
            };

        });

    </script>

</body>
</html>
