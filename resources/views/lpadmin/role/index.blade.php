<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <title>角色管理</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
        <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    </head>
    <body class="pear-container">
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form" action="">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">角色名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="name" placeholder="请输入角色名称" class="layui-input">
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
                            <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="role-query">
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
                <table id="role-table" lay-filter="role-table"></table>

                <script type="text/html" id="role-toolbar">
                    <div class="layui-btn-group">
                        <button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="add" style="margin-right: 5px;">
                            <i class="layui-icon layui-icon-add-1"></i>
                            新增
                        </button>
                        <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="batchRemove">
                            <i class="layui-icon layui-icon-delete"></i>
                            删除
                        </button>
                    </div>
                </script>

                <script type="text/html" id="role-status">
                    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="role-status" @{{# if(d.status == 1) { }} checked @{{# } }}>
                </script>

                <script type="text/html" id="role-toolbar-right">
                    <div style="white-space: nowrap; display: flex; gap: 4px; justify-content: center;">
                        <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                            <i class="layui-icon layui-icon-edit"></i>
                        </button>
                        <button class="table-action-btn table-action-permission" lay-event="permission" title="权限">
                            <i class="layui-icon layui-icon-vercode"></i>
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
            const SELECT_API = "{{ route('lpadmin.role.index') }}";
            const UPDATE_API = "{{ route('lpadmin.role.update', ':id') }}";
            const REMOVE_API = "{{ route('lpadmin.role.destroy', ':id') }}";
            const ADD_URL = "{{ route('lpadmin.role.create') }}";
            const EDIT_URL = "{{ route('lpadmin.role.edit', ':id') }}";
            const PERMISSION_URL = "{{ route('lpadmin.role.permissions', ':id') }}";
            const TOGGLE_STATUS_API = "{{ route('lpadmin.role.toggle_status', ':id') }}";
            const BATCH_REMOVE_API = "{{ route('lpadmin.role.destroy', ':id') }}";

            layui.use(['table', 'form', 'jquery', 'popup'], function () {
                let table = layui.table;
                let form = layui.form;
                let $ = layui.jquery;

                let cols = [
                    [
                        {type: 'checkbox'},
                        {title: 'ID', field: 'id', width: 80, align: 'center'},
                        {title: '角色名称', field: 'name', align: 'center'},
                        {title: '显示名称', field: 'display_name', align: 'center'},
                        {title: '描述', field: 'description', align: 'center'},
                        {title: '权限数量', field: 'permission_count', width: 100, align: 'center'},
                        {title: '管理员数量', field: 'admin_count', width: 120, align: 'center'},
                        {title: '状态', field: 'status', width: 100, align: 'center', templet: '#role-status'},
                        {title: '创建时间', field: 'created_at', width: 180, align: 'center'},
                        {title: '操作', width: 100, align: 'center', toolbar: '#role-toolbar-right', fixed: "right"}
                    ]
                ];

                table.render({
                    elem: '#role-table',
                    url: SELECT_API,
                    method: 'GET',
                    toolbar: '#role-toolbar',
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
                    limit: 20,
                    parseData: function(res) {
                        return {
                            code: res.code === 200 ? 0 : res.code,
                            msg: res.message,
                            count: res.total,
                            data: res.data
                        };
                    }
                });

                // 搜索
                form.on('submit(role-query)', function (data) {
                    table.reload('role-table', {
                        where: data.field,
                        page: {curr: 1}
                    });
                    return false;
                });

                // 工具栏事件
                table.on('toolbar(role-table)', function (obj) {
                    if (obj.event === 'add') {
                        layer.open({
                            type: 2,
                            title: '新增角色',
                            shade: 0.1,
                            area: ['500px', '600px'],
                            content: ADD_URL
                        });
                    } else if (obj.event === 'batchRemove') {
                        let checkStatus = table.checkStatus('role-table');
                        let data = checkStatus.data;
                        if (data.length === 0) {
                            layer.msg('请选择要删除的数据');
                            return;
                        }
                        let ids = data.map(item => item[PRIMARY_KEY]);
                        layer.confirm('确定删除选中的角色吗？', function (index) {
                            $.ajax({
                                url: BATCH_REMOVE_API.replace(':id', ids.join(',')),
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        table.reload('role-table');
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                }
                            });
                            layer.close(index);
                        });
                    } else if (obj.event === 'refresh') {
                        table.reload('role-table');
                    }
                });

                // 行工具事件
                table.on('tool(role-table)', function (obj) {
                    let data = obj.data;
                    if (obj.event === 'edit') {
                        layer.open({
                            type: 2,
                            title: '编辑角色',
                            shade: 0.1,
                            area: ['500px', '600px'],
                            content: EDIT_URL.replace(':id', data[PRIMARY_KEY])
                        });
                    } else if (obj.event === 'permission') {
                        layer.open({
                            type: 2,
                            title: '分配权限',
                            shade: 0.1,
                            area: ['600px', '700px'],
                            content: PERMISSION_URL.replace(':id', data[PRIMARY_KEY])
                        });
                    } else if (obj.event === 'remove') {
                        layer.confirm('确定删除该角色吗？', function (index) {
                            $.ajax({
                                url: REMOVE_API.replace(':id', data[PRIMARY_KEY]),
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        table.reload('role-table');
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
                form.on('switch(role-status)', function (obj) {
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
                    table.reload('role-table');
                };
            });

        </script>

    </body>
</html>
