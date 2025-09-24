<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <title>权限规则管理</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
        <link rel="stylesheet" href="/static/admin/css/table-common.css" />
        <style>
            /* 表格容器100%宽度 */
            .layui-card-body {
                padding: 15px;
            }

            .layui-table-view {
                width: 100% !important;
            }

            .layui-table {
                width: 100% !important;
            }

            /* 优化按钮间距 */
            .layui-btn-group .pear-btn {
                margin-right: 5px;
            }
            .layui-btn-group .pear-btn:last-child {
                margin-right: 0;
            }

            /* 优化表格行高 */
            .layui-table-body tr {
                height: 45px;
            }

            /* 权限标识列不换行 */
            .layui-table tbody tr td:nth-child(4) {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 250px;
            }

            /* 操作按钮组优化 */
            .layui-table .layui-btn-group,
            .layui-table [lay-event] {
                white-space: nowrap;
            }

            /* 操作列固定宽度 */
            .layui-table tbody tr td:last-child {
                white-space: nowrap;
                overflow: visible;
            }

            /* 小按钮样式优化 */
            .layui-btn-xs {
                height: 22px;
                line-height: 22px;
                padding: 0 8px;
                font-size: 12px;
            }

            /* 树形表格图标优化 */
            .treeTable-icon {
                cursor: pointer;
                margin-right: 5px;
            }

            /* 树形表格缩进 */
            .treeTable .layui-table-cell {
                height: auto;
                line-height: 28px;
            }

            /* 图标列样式优化 */
            .layui-table tbody tr td:nth-child(9) {
                padding: 8px 5px;
            }

            /* 图标预览样式 */
            .icon-preview {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                margin-left: 10px;
                padding: 2px 6px;
                background: #f0f9ff;
                border: 1px solid #e1f5fe;
                border-radius: 3px;
                font-size: 12px;
            }
        </style>
    </head>
    <body class="pear-container">
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form" action="">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">权限名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="title" placeholder="请输入权限名称" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">权限标识</label>
                            <div class="layui-input-inline">
                                <input type="text" name="name" placeholder="请输入权限标识" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-inline">
                                <select name="type">
                                    <option value="">全部类型</option>
                                    <option value="menu">菜单</option>
                                    <option value="button">按钮</option>
                                    <option value="api">接口</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <select name="status">
                                    <option value="">全部状态</option>
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="rule-query">
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

        <div class="layui-card">
            <div class="layui-card-body">
                <table id="rule-table" lay-filter="rule-table"></table>

                <script type="text/html" id="rule-toolbar">
                    <div class="layui-btn-group" style="margin-right: 10px;">
                        <button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="add" style="margin-right: 5px;">
                            <i class="layui-icon layui-icon-add-1"></i>
                            新增
                        </button>
                        <button class="pear-btn pear-btn-success pear-btn-sm" lay-event="expand" style="margin-right: 5px;">
                            <i class="layui-icon layui-icon-spread-left"></i>
                            展开全部
                        </button>
                        <button class="pear-btn pear-btn-warning pear-btn-sm" lay-event="collapse" style="margin-right: 5px;">
                            <i class="layui-icon layui-icon-shrink-right"></i>
                            收起全部
                        </button>
                        <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="batchRemove">
                            <i class="layui-icon layui-icon-delete"></i>
                            删除
                        </button>
                    </div>
                </script>

                <script type="text/html" id="rule-type">
                    @{{# if(d.type === 'menu') { }}
                        <span class="layui-badge layui-bg-blue">菜单</span>
                    @{{# } else if(d.type === 'button') { }}
                        <span class="layui-badge layui-bg-orange">按钮</span>
                    @{{# } else if(d.type === 'api') { }}
                        <span class="layui-badge layui-bg-green">接口</span>
                    @{{# } else { }}
                        <span class="layui-badge layui-bg-gray">未知</span>
                    @{{# } }}
                </script>

                <script type="text/html" id="rule-icon">
                    @{{# if(d.icon) { }}
                        <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-direction: column;">
                            <i class="layui-icon @{{d.icon}}" style="font-size: 18px; color: #1890ff;"></i>
                            <span style="font-size: 11px; color: #999; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="@{{d.icon}}">@{{d.icon}}</span>
                        </div>
                    @{{# } else { }}
                        <span style="color: #ccc; font-size: 12px;">无图标</span>
                    @{{# } }}
                </script>

                <script type="text/html" id="rule-status">
                    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="rule-status" @{{# if(d.status == 1) { }} checked @{{# } }}>
                </script>

                <script type="text/html" id="rule-toolbar-right">
                    <div style="white-space: nowrap; display: flex; gap: 3px; justify-content: center;">
                        <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                            <i class="layui-icon layui-icon-edit"></i>
                        </button>
                        <button class="table-action-btn table-action-add" lay-event="add-child" title="添加子权限">
                            <i class="layui-icon layui-icon-add-1"></i>
                        </button>
                        <button class="table-action-btn table-action-delete" lay-event="remove" title="删除">
                            <i class="layui-icon layui-icon-delete"></i>
                        </button>
                    </div>
                </script>
            </div>
        </div>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script>

            // 相关常量
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.rule.index') }}";
            const UPDATE_API = "{{ route('lpadmin.rule.update', ':id') }}";
            const REMOVE_API = "{{ route('lpadmin.rule.destroy', ':id') }}";
            const ADD_URL = "{{ route('lpadmin.rule.create') }}";
            const EDIT_URL = "{{ route('lpadmin.rule.edit', ':id') }}";
            const TOGGLE_STATUS_API = "{{ route('lpadmin.rule.toggle_status', ':id') }}";
            const BATCH_REMOVE_API = "{{ route('lpadmin.rule.destroy', ':id') }}";

            layui.use(['table', 'form', 'jquery', 'treetable', 'popup'], function () {
                let table = layui.table;
                let treetable = layui.treetable;
                let form = layui.form;
                let $ = layui.jquery;

                let cols = [
                    [
                        {type: 'checkbox'},
                        {title: 'ID', field: 'id', width: 80, align: 'center'},
                        {title: '权限名称', field: 'title', width: 160},
                        {title: '权限标识', field: 'name', width: 250, align: 'left', style: 'white-space: nowrap;'},
                        {title: '类型', field: 'type', width: 80, align: 'center', templet: '#rule-type'},
                        {title: '路由/URL', field: 'url', width: 160, align: 'left'},
                        {title: '图标', field: 'icon', width: 120, align: 'center', templet: '#rule-icon'},
                        {title: '排序', field: 'sort', width: 60, align: 'center'},
                        {title: '状态', field: 'status', width: 90, align: 'center', templet: '#rule-status'},
                        {title: '创建时间', field: 'created_at', width: 180, align: 'center'},
                        {title: '操作', width: 100, align: 'center', toolbar: '#rule-toolbar-right', fixed: 'right'}
                    ]
                ];

                treetable.render({
                    elem: '#rule-table',
                    url: SELECT_API,
                    method: 'GET',
                    toolbar: '#rule-toolbar',
                    defaultToolbar: [{
                        title: '刷新',
                        layEvent: 'refresh',
                        icon: 'layui-icon-refresh',
                    }, 'filter', 'print', 'exports'],
                    cols: cols,
                    skin: 'line',
                    size: 'lg',
                    treeColIndex: 2,
                    treeIdName: 'id',
                    treePidName: 'parent_id',
                    treeDefaultClose: true,
                    treeLinkage: true,
                    page: false,
                    parseData: function(res) {
                        return {
                            code: res.code === 200 ? 0 : res.code,
                            msg: res.message,
                            data: res.data
                        };
                    }
                });

                // 搜索
                form.on('submit(rule-query)', function (data) {
                    // 重新渲染表格，传递搜索参数
                    treetable.render({
                        elem: '#rule-table',
                        url: SELECT_API,
                        method: 'GET',
                        where: data.field,
                        toolbar: '#rule-toolbar',
                        defaultToolbar: [{
                            title: '刷新',
                            layEvent: 'refresh',
                            icon: 'layui-icon-refresh',
                        }, 'filter', 'print', 'exports'],
                        cols: cols,
                        skin: 'line',
                        size: 'lg',
                        treeColIndex: 2,
                        treeIdName: 'id',
                        treePidName: 'parent_id',
                        treeDefaultClose: true,
                        treeLinkage: true,
                        page: false,
                        parseData: function(res) {
                            return {
                                code: res.code === 200 ? 0 : res.code,
                                msg: res.message,
                                data: res.data
                            };
                        }
                    });
                    return false;
                });

                // 工具栏事件
                table.on('toolbar(rule-table)', function (obj) {
                    if (obj.event === 'add') {
                        layer.open({
                            type: 2,
                            title: '新增权限',
                            shade: 0.1,
                            area: ['600px', '700px'],
                            content: ADD_URL
                        });
                    } else if (obj.event === 'expand') {
                        // 展开全部节点
                        treetable.expandAll('#rule-table');
                    } else if (obj.event === 'collapse') {
                        // 收起全部节点
                        treetable.foldAll('#rule-table');
                    } else if (obj.event === 'batchRemove') {
                        let checkStatus = table.checkStatus('rule-table');
                        let data = checkStatus.data;
                        if (data.length === 0) {
                            layer.msg('请选择要删除的数据');
                            return;
                        }
                        let ids = data.map(item => item[PRIMARY_KEY]);
                        layer.confirm('确定删除选中的权限吗？', function (index) {
                            $.ajax({
                                url: BATCH_REMOVE_API.replace(':id', ids.join(',')),
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        treetable.reload('#rule-table');
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                }
                            });
                            layer.close(index);
                        });
                    } else if (obj.event === 'refresh') {
                        treetable.reload('#rule-table');
                    }
                });

                // 行工具事件
                table.on('tool(rule-table)', function (obj) {
                    let data = obj.data;
                    if (obj.event === 'edit') {
                        layer.open({
                            type: 2,
                            title: '编辑权限',
                            shade: 0.1,
                            area: ['600px', '700px'],
                            content: EDIT_URL.replace(':id', data[PRIMARY_KEY])
                        });
                    } else if (obj.event === 'add-child') {
                        layer.open({
                            type: 2,
                            title: '添加子权限',
                            shade: 0.1,
                            area: ['600px', '700px'],
                            content: ADD_URL + '?parent_id=' + data[PRIMARY_KEY]
                        });
                    } else if (obj.event === 'remove') {
                        layer.confirm('确定删除该权限吗？', function (index) {
                            $.ajax({
                                url: REMOVE_API.replace(':id', data[PRIMARY_KEY]),
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        treeTable.reload('rule-table');
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                }
                            });
                            layer.close(index);
                        });
                    }
                });

                // 状态切换
                form.on('switch(rule-status)', function (obj) {
                    let id = this.value;
                    let status = obj.elem.checked ? 1 : 0;

                    $.ajax({
                        url: TOGGLE_STATUS_API.replace(':id', id),
                        type: 'POST',
                        data: {status: status},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            if (res.code === 0) {
                                layer.msg(res.message, {icon: 1});
                            } else {
                                layer.msg(res.message, {icon: 2});
                                // 恢复开关状态
                                obj.elem.checked = !obj.elem.checked;
                                form.render('checkbox');
                            }
                        }
                    });
                });

                // 全局刷新表格函数
                window.refreshTable = function() {
                    treetable.reload('#rule-table');
                };
            });

        </script>

    </body>
</html>
