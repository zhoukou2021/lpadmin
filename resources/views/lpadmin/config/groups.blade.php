<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>配置分组管理</title>
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
                        <label class="layui-form-label">分组名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" placeholder="请输入分组名称" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="group-query">
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
            <table id="group-table" lay-filter="group-table"></table>
        </div>
    </div>

    <script type="text/html" id="group-toolbar">
        <div class="layui-btn-group">
            <button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="add">
                <i class="layui-icon layui-icon-add-1"></i>
                新增
            </button>
            <button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="batchRemove">
                <i class="layui-icon layui-icon-delete"></i>
                删除
            </button>
        </div>
    </script>

    <script type="text/html" id="group-toolbar-right">
        <div style="white-space: nowrap; display: flex; gap: 3px; justify-content: center;">
            <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                <i class="layui-icon layui-icon-edit"></i>
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
        const PRIMARY_KEY = "name";
        const SELECT_API = "/lpadmin/config/groups"; // GET
        const CREATE_API = "/lpadmin/config/groups/create-groups"; // POST
        const UPDATE_API = "/lpadmin/config/groups/"; // PUT {group}
        const REMOVE_API = "/lpadmin/config/groups/del-groups/"; // DELETE {group}
        const BATCH_REMOVE_API = "/lpadmin/config/groups/groups-batch"; // DELETE

        layui.use(['table', 'form', 'jquery', 'popup'], function () {
            let table = layui.table;
            let form = layui.form;
            let $ = layui.jquery;

            let cols = [
                [
                    {type: 'checkbox'},
                    {title: '分组名称', field: 'name', width: 150},
                    {title: '分组标题', field: 'title', width: 200},
                    {title: '分组描述', field: 'description', width: 250},
                    {title: '配置数量', field: 'config_count', width: 120, align: 'center'},
                    {title: '操作', width: 100, align: 'center', toolbar: '#group-toolbar-right', fixed: 'right'}
                ]
            ];

            table.render({
                elem: '#group-table',
                url: SELECT_API,
                method: 'GET',
                toolbar: '#group-toolbar',
                defaultToolbar: [{
                    title: '刷新',
                    layEvent: 'refresh',
                    icon: 'layui-icon-refresh',
                }, 'filter', 'print', 'exports'],
                cols: cols,
                skin: 'line',
                size: 'lg',
                page: false,
                parseData: function(res) {
                    return {
                        code: res.code,
                        msg: res.message,
                        count: res.data ? res.data.length : 0,
                        data: res.data || []
                    };
                }
            });

            // 搜索
            form.on('submit(group-query)', function (data) {
                table.reload('group-table', {
                    where: data.field,
                    page: {curr: 1}
                });
                return false;
            });

            // 工具栏事件
            table.on('toolbar(group-table)', function (obj) {
                if (obj.event === 'add') {
                    showGroupForm();
                } else if (obj.event === 'batchRemove') {
                    const checkStatus = table.checkStatus('group-table');
                    if (checkStatus.data.length === 0) {
                        layer.msg('请选择要删除的数据', {icon: 2});
                        return;
                    }

                    layer.confirm('确定要删除选中的 ' + checkStatus.data.length + ' 个分组吗？', {
                        icon: 3,
                        title: '提示'
                    }, function(index) {
                        const names = checkStatus.data.map(item => item[PRIMARY_KEY]);
                        batchDelete(names);
                        layer.close(index);
                    });
                } else if (obj.event === 'refresh') {
                    table.reload('group-table');
                }
            });

            // 行工具事件
            table.on('tool(group-table)', function (obj) {
                const data = obj.data;

                if (obj.event === 'edit') {
                    showGroupForm(data);
                } else if (obj.event === 'remove') {
                    layer.confirm('确定要删除分组 "' + data.title + '" 吗？', {
                        icon: 3,
                        title: '提示'
                    }, function(index) {
                        deleteGroup(data.name, obj);
                        layer.close(index);
                    });
                }
            });

            // 显示分组表单
            function showGroupForm(data = null) {
                const isEdit = data !== null;
                const title = isEdit ? '编辑分组' : '新增分组';
                
                const formHtml = `
                    <form class="layui-form" lay-filter="group-form" style="padding: 20px;">
                        <div class="layui-form-item">
                            <label class="layui-form-label">分组名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="name" value="${data ? data.name : ''}" 
                                       placeholder="请输入分组名称（英文）" class="layui-input" 
                                       lay-verify="required" ${isEdit ? 'readonly' : ''}>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">分组标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="title" value="${data ? data.title : ''}" 
                                       placeholder="请输入分组标题（中文）" class="layui-input" lay-verify="required">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">分组描述</label>
                            <div class="layui-input-block">
                                <textarea name="description" placeholder="请输入分组描述" 
                                          class="layui-textarea">${data ? data.description : ''}</textarea>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button type="submit" class="layui-btn" lay-submit lay-filter="submit">
                                    <i class="layui-icon layui-icon-ok"></i> 保存
                                </button>
                                <button type="button" class="layui-btn layui-btn-primary" onclick="layer.closeAll()">
                                    <i class="layui-icon layui-icon-close"></i> 取消
                                </button>
                            </div>
                        </div>
                    </form>
                `;

                layer.open({
                    type: 1,
                    title: title,
                    content: formHtml,
                    area: ['500px', '400px'],
                    success: function() {
                        form.render();
                        
                        // 表单提交
                        form.on('submit(submit)', function(formData) {
                            const url = isEdit ? `/lpadmin/config/groups/${data.name}` : CREATE_API;
                            const method = isEdit ? 'PUT' : 'POST';
                            
                            $.ajax({
                                url: url,
                                method: method,
                                data: formData.field,
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(result) {
                                    if (result.code === 0) {
                                        layer.msg(result.message, {icon: 1});
                                        layer.closeAll();
                                        table.reload('group-table');
                                    } else {
                                        layer.msg(result.message, {icon: 2});
                                    }
                                },
                                error: function() {
                                    layer.msg('操作失败', {icon: 2});
                                }
                            });
                            
                            return false;
                        });
                    }
                });
            }

            // 删除分组
            function deleteGroup(name, obj) {
                $.ajax({
                    url: REMOVE_API + name,
                    method: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (result && result.code === 0) {
                            layer.msg(result.message || '操作成功', {icon: 1});
                            if (obj) {
                                obj.del();
                            } else {
                                table.reload('group-table');
                            }
                        } else {
                            layer.msg((result && result.message) ? result.message : '操作失败', {icon: 2});
                        }
                    },
                    error: function(xhr) {
                        var msg = '网络错误';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr && xhr.responseText) {
                            try {
                                var json = JSON.parse(xhr.responseText);
                                if (json && json.message) msg = json.message;
                            } catch (e) {}
                        }
                        layer.msg(msg, {icon: 2});
                    }
                });
            }

            // 批量删除
            function batchDelete(names) {
                $.ajax({
                    url: BATCH_REMOVE_API,
                    method: 'DELETE',
                    dataType: 'json',
                    data: {names: names},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (result && result.code === 0) {
                            layer.msg(result.message || '操作成功', {icon: 1});
                            table.reload('group-table');
                        } else {
                            layer.msg((result && result.message) ? result.message : '操作失败', {icon: 2});
                        }
                    },
                    error: function(xhr) {
                        var msg = '网络错误';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr && xhr.responseText) {
                            try {
                                var json = JSON.parse(xhr.responseText);
                                if (json && json.message) msg = json.message;
                            } catch (e) {}
                        }
                        layer.msg(msg, {icon: 2});
                    }
                });
            }

            // 刷新表格函数（供父页面调用）
            window.refreshTable = function() {
                table.reload('group-table');
            };
        });
    </script>

</body>
</html>
