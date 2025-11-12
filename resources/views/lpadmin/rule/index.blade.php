<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>菜单管理</title>
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

        /* 可编辑列样式 - 保持Layui原生样式 */
        .layui-table-body td[data-field="sort"] {
            cursor: pointer;
        }
    </style>
</head>
<body class="pear-container">

    <!-- 顶部查询表单 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form top-search-from">

                <div class="layui-form-item">
                    <label class="layui-form-label">菜单标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">菜单标识</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <select name="status">
                            <option value="">全部状态</option>
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-block">
                        <select name="type">
                            <option value="">全部类型</option>
                            <option value="menu">菜单</option>
                            <option value="button">按钮</option>
                            <option value="api">接口</option>
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search"></i>
                        搜索
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        <i class="layui-icon layui-icon-refresh"></i>
                        重置
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <table id="menu-table" lay-filter="menu-table"></table>
        </div>
    </div>

    <!-- 表格顶部工具栏 -->
    <script type="text/html" id="table-toolbar">
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

    <!-- 表格行工具栏 -->
    <script type="text/html" id="table-bar">
        <div style="white-space: nowrap; display: flex; gap: 4px; justify-content: center;">
            <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                <i class="layui-icon layui-icon-edit"></i>
            </button>
            <button class="table-action-btn table-action-add" lay-event="add-child" title="添加子菜单">
                <i class="layui-icon layui-icon-add-1"></i>
            </button>
            <button class="table-action-btn table-action-delete" lay-event="remove" title="删除">
                <i class="layui-icon layui-icon-delete"></i>
            </button>
        </div>
    </script>



    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>

        // 相关常量
        const PRIMARY_KEY = "id";
        const SELECT_API = "{{ route('lpadmin.menu.select') }}";
        const DELETE_API = "{{ route('lpadmin.menu.destroy', ':id') }}";
        const BATCH_DELETE_API = "{{ route('lpadmin.menu.batchDestroy') }}";
        const UPDATE_SORT_API = "{{ route('lpadmin.menu.updateSort', ':id') }}";
        const INSERT_URL = "{{ route('lpadmin.menu.create') }}";
        const UPDATE_URL = "{{ route('lpadmin.menu.edit', ':id') }}";

        // 表格渲染
        layui.use(['table', 'form', 'jquery', 'treetable', 'popup'], function() {
            let table = layui.table;
            let treetable = layui.treetable;
            let form = layui.form;
            let $ = layui.jquery;

            // 表头参数
            let cols = [
                [
                    {type: "checkbox"},
                    {title: "ID", field: "id", width: 80, align: "center"},
                    {
                        title: "菜单标题",
                        field: "title",
                        width: 200,
                        templet: function(d) {
                            let iconHtml = '';
                            if (d.icon) {
                                iconHtml = '<i class="layui-icon ' + d.icon + '" style="font-size: 16px; margin-right: 8px;"></i>';
                            }
                            return iconHtml + d.title;
                        }
                    },
                    {title: "菜单标识", field: "name", width: 180},
                    {
                        title: "排序",
                        field: "sort",
                        width: 100,
                        align: "center",
                        sort: false, // 禁用列排序，避免破坏树形结构
                        edit: true
                    },
                    {
                        title: "类型",
                        field: "type",
                        width: 80,
                        align: "center",
                        templet: function(d) {
                            if (d.type == 'menu') {
                                return '<span class="layui-badge layui-bg-green">菜单</span>';
                            } else if (d.type == 'button') {
                                return '<span class="layui-badge layui-bg-orange">按钮</span>';
                            } else if (d.type == 'api') {
                                return '<span class="layui-badge layui-bg-blue">接口</span>';
                            } else {
                                return '<span class="layui-badge layui-bg-gray">未知</span>';
                            }
                        }
                    },
                    {title: "链接", field: "url", width: 200},
                    {
                        title: "显示",
                        field: "is_show",
                        width: 80,
                        align: "center",
                        templet: function(d) {
                            if (d.is_show == 1) {
                                return '<span class="layui-badge layui-bg-green">显示</span>';
                            } else {
                                return '<span class="layui-badge layui-bg-orange">隐藏</span>';
                            }
                        }
                    },
                    {
                        title: "状态",
                        field: "status",
                        width: 80,
                        align: "center",
                        templet: function(d) {
                            if (d.status == 1) {
                                return '<span class="layui-badge layui-bg-green">启用</span>';
                            } else {
                                return '<span class="layui-badge layui-bg-gray">禁用</span>';
                            }
                        }
                    },
                    {title: "创建时间", field: "created_at", width: 160, align: "center"},
                    {title: "操作", width: 100, align: "center", toolbar: "#table-bar", fixed: "right"}
                ]
            ];

            // 渲染表格
            treetable.render({
                elem: "#menu-table",
                url: SELECT_API,
                method: "GET",
                toolbar: "#table-toolbar",
                defaultToolbar: [{
                    title: '刷新',
                    layEvent: 'refresh',
                    icon: 'layui-icon-refresh',
                }, "filter", "exports", "print"],
                cols: cols,
                skin: "line",
                size: "lg",
                treeColIndex: 2,
                treeIdName: "id",
                treePidName: "parent_id",
                treeDefaultClose: true,
                treeLinkage: true,
                page: false,
                // 移除 initSort 配置，避免破坏树形结构
                // 排序由后端控制，保持树形层级关系
                parseData: function(res) {
                    return {
                        code: res.code === 0 ? 0 : 1,
                        msg: res.message,
                        data: res.data || []
                    };
                }
            });

            // 搜索
            form.on('submit(search)', function (data) {
                treetable.reload("#menu-table", {
                    where: data.field
                });
                return false;
            });

            // 工具栏事件
            table.on('toolbar(menu-table)', function (obj) {
                if (obj.event === 'add') {
                    layer.open({
                        type: 2,
                        title: '新增菜单',
                        shade: 0.1,
                        area: ['800px', '600px'],
                        content: INSERT_URL
                    });
                } else if (obj.event === 'expand') {
                    // 展开全部节点
                    treetable.expandAll('#menu-table');
                } else if (obj.event === 'collapse') {
                    // 收起全部节点
                    treetable.foldAll('#menu-table');
                } else if (obj.event === 'batchRemove') {
                    let checkStatus = table.checkStatus('menu-table');
                    let data = checkStatus.data;
                    if (data.length === 0) {
                        layer.msg('请选择要删除的数据', {icon: 2});
                        return;
                    }
                    let ids = data.map(item => item[PRIMARY_KEY]);
                    layer.confirm('确定删除选中的菜单吗？', {icon: 3, title: '提示'}, function (index) {
                        $.ajax({
                            url: BATCH_DELETE_API,
                            method: 'DELETE',
                            data: { ids: ids },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (result) {
                                if (result.code === 0) {
                                    layer.msg(result.message, {icon: 1});
                                    treetable.reload("#menu-table");
                                } else {
                                    layer.msg(result.message, {icon: 2});
                                }
                            },
                            error: function () {
                                layer.msg('网络异常', {icon: 2});
                            }
                        });
                        layer.close(index);
                    });
                } else if (obj.event === 'refresh') {
                    treetable.reload('#menu-table');
                }
            });

            // 行工具事件
            table.on('tool(menu-table)', function (obj) {
                let data = obj.data;
                if (obj.event === 'edit') {
                    let editUrl = UPDATE_URL.replace(':id', data[PRIMARY_KEY]);
                    layer.open({
                        type: 2,
                        title: '编辑菜单',
                        shade: 0.1,
                        area: ['800px', '600px'],
                        content: editUrl
                    });
                } else if (obj.event === 'add-child') {
                    let createUrl = INSERT_URL + '?parent_id=' + data[PRIMARY_KEY];
                    layer.open({
                        type: 2,
                        title: '添加子菜单',
                        shade: 0.1,
                        area: ['800px', '600px'],
                        content: createUrl
                    });
                } else if (obj.event === 'remove') {
                    layer.confirm('确定删除该菜单吗？', {icon: 3, title: '提示'}, function (index) {
                        let deleteUrl = DELETE_API.replace(':id', data[PRIMARY_KEY]);
                        $.ajax({
                            url: deleteUrl,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (result) {
                                if (result.code === 0) {
                                    layer.msg(result.message, {icon: 1});
                                    treetable.reload("#menu-table");
                                } else {
                                    layer.msg(result.message, {icon: 2});
                                }
                            },
                            error: function () {
                                layer.msg('网络异常', {icon: 2});
                            }
                        });
                        layer.close(index);
                    });
                }
            });

            // 监听排序列编辑事件
            table.on('edit(menu-table)', function(obj) {
                let data = obj.data;
                let field = obj.field;
                let value = obj.value;

                // 只处理排序字段的编辑
                if (field === 'sort') {
                    // 验证输入值
                    if (!/^\d+$/.test(value) || parseInt(value) < 0 || parseInt(value) > 9999) {
                        layer.msg('排序值必须是0-9999之间的整数', {icon: 2});
                        // 恢复原值
                        obj.reedit();
                        return;
                    }

                    // 发送更新请求
                    let updateUrl = UPDATE_SORT_API.replace(':id', data[PRIMARY_KEY]);
                    $.ajax({
                        url: updateUrl,
                        method: 'PATCH',
                        data: {
                            sort: parseInt(value)
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (result) {
                            if (result.code === 0) {
                                layer.msg('排序更新成功', {icon: 1, time: 1500});
                                // 重新加载表格以反映排序变化
                                setTimeout(function() {
                                    treetable.reload("#menu-table");
                                }, 1000);
                            } else {
                                layer.msg(result.message || '更新失败', {icon: 2});
                                // 恢复原值
                                obj.reedit();
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = '网络异常';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            layer.msg(errorMsg, {icon: 2});
                            // 恢复原值
                            obj.reedit();
                        }
                    });
                }
            });

            // 刷新表格
            window.refreshTable = function () {
                treetable.reload("#menu-table");
            };

        });

    </script>

</body>
</html>
