<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>用户管理</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
        <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    </head>
    <body class="pear-container">
        <!-- 统计信息卡片 -->
        <div class="layui-row layui-col-space15" style="margin-bottom: 15px;">
            <div class="layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-body">
                        <div class="layui-row">
                            <div class="layui-col-xs8">
                                <div style="color: #666; font-size: 14px;">总用户数</div>
                                <div style="font-size: 24px; font-weight: bold; color: #1890ff;" id="total-users">-</div>
                            </div>
                            <div class="layui-col-xs4" style="text-align: right;">
                                <i class="layui-icon layui-icon-user" style="font-size: 40px; color: #1890ff;"></i>
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
                                <div style="color: #666; font-size: 14px;">今日新增</div>
                                <div style="font-size: 24px; font-weight: bold; color: #52c41a;" id="today-new">-</div>
                            </div>
                            <div class="layui-col-xs4" style="text-align: right;">
                                <i class="layui-icon layui-icon-add-circle" style="font-size: 40px; color: #52c41a;"></i>
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
                                <div style="color: #666; font-size: 14px;">启用用户</div>
                                <div style="font-size: 24px; font-weight: bold; color: #13c2c2;" id="enabled-users">-</div>
                            </div>
                            <div class="layui-col-xs4" style="text-align: right;">
                                <i class="layui-icon layui-icon-ok-circle" style="font-size: 40px; color: #13c2c2;"></i>
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
                                <div style="color: #666; font-size: 14px;">禁用用户</div>
                                <div style="font-size: 24px; font-weight: bold; color: #ff4d4f;" id="disabled-users">-</div>
                            </div>
                            <div class="layui-col-xs4" style="text-align: right;">
                                <i class="layui-icon layui-icon-close-fill" style="font-size: 40px; color: #ff4d4f;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form" action="">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">用户名</label>
                            <div class="layui-input-inline">
                                <input type="text" name="username" placeholder="请输入用户名" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">邮箱</label>
                            <div class="layui-input-inline">
                                <input type="text" name="email" placeholder="请输入邮箱" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">手机号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="phone" placeholder="请输入手机号" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <select name="status">
                                    <option value="">全部状态</option>
                                    <option value="1">正常</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button type="submit" class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="user-query">
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
                <table id="user-table" lay-filter="user-table"></table>

                <script type="text/html" id="user-toolbar">
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

                <script type="text/html" id="user-avatar">
                    @{{# if(d.avatar) { }}
                        <img src="@{{d.avatar}}" alt="头像" style="width: 40px; height: 40px; border-radius: 50%;">
                    @{{# } else { }}
                        <img src="/static/admin/images/avatar.jpg" alt="默认头像" style="width: 40px; height: 40px; border-radius: 50%;">
                    @{{# } }}
                </script>

                <script type="text/html" id="user-status">
                    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="正常|禁用" lay-filter="user-status" @{{# if(d.status == 1) { }} checked @{{# } }}>
                </script>

                <script type="text/html" id="user-toolbar-right">
                    <div style="white-space: nowrap; display: flex; gap: 3px; justify-content: center;">
                        <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                            <i class="layui-icon layui-icon-edit"></i>
                        </button>
                        <button class="table-action-btn table-action-view" lay-event="view" title="查看">
                            <i class="layui-icon layui-icon-eye"></i>
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
            const SELECT_API = "{{ route('lpadmin.user.index') }}";
            const UPDATE_API = "{{ route('lpadmin.user.update', ':id') }}";
            const REMOVE_API = "{{ route('lpadmin.user.destroy', ':id') }}";
            const ADD_URL = "{{ route('lpadmin.user.create') }}";
            const EDIT_URL = "{{ route('lpadmin.user.edit', ':id') }}";
            const VIEW_URL = "{{ route('lpadmin.user.show', ':id') }}";
            const TOGGLE_STATUS_API = "{{ route('lpadmin.user.toggle_status', ':id') }}";
            const BATCH_REMOVE_API = "{{ route('lpadmin.user.batch_delete') }}";
            const STATISTICS_API = "{{ route('lpadmin.user.statistics') }}";

            layui.use(['table', 'form', 'jquery', 'popup'], function () {
                let table = layui.table;
                let form = layui.form;
                let $ = layui.jquery;

                let cols = [
                    [
                        {type: 'checkbox'},
                        {title: 'ID', field: 'id', width: 80, align: 'center'},
                        {title: '头像', field: 'avatar', width: 80, align: 'center', templet: '#user-avatar'},
                        {title: '用户名', field: 'username', width: 120, align: 'center'},
                        {title: '昵称', field: 'nickname', width: 120, align: 'center'},
                        {title: '邮箱', field: 'email', width: 180, align: 'center'},
                        {title: '手机号', field: 'phone', width: 120, align: 'center'},
                        {title: '性别', field: 'gender', width: 80, align: 'center',
                        templet: function (d) {
                            let sexs = {
                                0: '女',
                                1: '男', 
                                2: '保密'
                            };
                            return sexs[d.gender];
                        }},
                        {title: '状态', field: 'status', width: 100, align: 'center', templet: '#user-status'},
                        {title: '注册时间', field: 'created_at', width: 180, align: 'center'},
                        {title: '最后登录', field: 'last_login_at', width: 180, align: 'center'},
                        {title: '操作', width: 100, align: 'center', toolbar: '#user-toolbar-right', fixed: 'right'}
                    ]
                ];

                table.render({
                    elem: '#user-table',
                    url: SELECT_API,
                    method: 'GET',
                    toolbar: '#user-toolbar',
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
                    limit: 20
                });

                // 搜索
                form.on('submit(user-query)', function (data) {
                    table.reload('user-table', {
                        where: data.field,
                        page: {curr: 1}
                    });
                    return false;
                });

                // 工具栏事件
                table.on('toolbar(user-table)', function (obj) {
                    if (obj.event === 'add') {
                        layer.open({
                            type: 2,
                            title: '新增用户',
                            shade: 0.1,
                            area: ['70%', '90%'],
                            content: ADD_URL
                        });
                    } else if (obj.event === 'batchRemove') {
                        let checkStatus = table.checkStatus('user-table');
                        let data = checkStatus.data;
                        if (data.length === 0) {
                            layer.msg('请选择要删除的数据');
                            return;
                        }
                        let ids = data.map(item => item[PRIMARY_KEY]);
                        layer.confirm('确定删除选中的用户吗？', function (index) {
                            $.ajax({
                                url: BATCH_REMOVE_API,
                                type: 'POST',
                                data: {
                                    ids: ids,
                                    _token: '{{ csrf_token() }}'
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        table.reload('user-table');
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                }
                            });
                            layer.close(index);
                        });
                    } else if (obj.event === 'refresh') {
                        table.reload('user-table');
                    }
                });

                // 行工具事件
                table.on('tool(user-table)', function (obj) {
                    let data = obj.data;
                    if (obj.event === 'edit') {
                        layer.open({
                            type: 2,
                            title: '编辑用户',
                            shade: 0.1,
                            area: ['600px', '700px'],
                            content: EDIT_URL.replace(':id', data[PRIMARY_KEY])
                        });
                    } else if (obj.event === 'view') {
                        layer.open({
                            type: 2,
                            title: '用户详情',
                            shade: 0.1,
                            area: ['600px', '700px'],
                            content: VIEW_URL.replace(':id', data[PRIMARY_KEY])
                        });
                    } else if (obj.event === 'remove') {
                        layer.confirm('确定删除该用户吗？', function (index) {
                            $.ajax({
                                url: REMOVE_API.replace(':id', data[PRIMARY_KEY]),
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        table.reload('user-table');
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
                form.on('switch(user-status)', function (obj) {
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

                // 加载统计数据
                function loadStatistics() {
                    $.ajax({
                        url: STATISTICS_API,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            if (res.code === 0) {
                                const stats = res.data;
                                $('#total-users').text(stats.total_users || 0);
                                $('#today-new').text(stats.today_new_users || 0);
                                $('#enabled-users').text(stats.enabled_users || 0);
                                $('#disabled-users').text(stats.disabled_users || 0);
                            }
                        },
                        error: function () {
                            console.log('加载统计数据失败');
                        }
                    });
                }

                // 页面加载时获取统计数据
                loadStatistics();

                // 全局刷新表格函数
                window.refreshTable = function() {
                    table.reload('user-table');
                    loadStatistics(); // 刷新表格时也刷新统计数据
                };
            });

        </script>

    </body>
</html>
