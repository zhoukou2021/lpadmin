<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>系统日志管理</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/table-common.css" />
</head>
<body class="pear-container">
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">
                        <span class="layui-icon layui-icon-file"></span>
                        系统日志管理
                    </div>
                    <div class="layui-card-body">
                    <!-- 搜索表单 -->
                    <form class="layui-form" lay-filter="searchForm">
                        <div class="layui-row layui-col-space10">
                            <div class="layui-col-md3">
                                <input type="text" name="action" placeholder="操作类型" class="layui-input">
                            </div>
                            <div class="layui-col-md3">
                                <input type="text" name="admin_username" placeholder="操作用户" class="layui-input">
                            </div>
                            <div class="layui-col-md3">
                                <input type="text" name="ip" placeholder="IP地址" class="layui-input">
                            </div>
                            <div class="layui-col-md3">
                                <button type="submit" class="layui-btn" lay-submit lay-filter="search">
                                    <i class="layui-icon layui-icon-search"></i> 搜索
                                </button>
                                <button type="reset" class="layui-btn layui-btn-primary">
                                    <i class="layui-icon layui-icon-refresh"></i> 重置
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- 数据表格 -->
                    <table class="layui-hide" id="logTable" lay-filter="logTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 工具栏模板 -->
<script type="text/html" id="toolbarDemo">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-danger layui-btn-sm" lay-event="batchDelete">批量删除</button>
        <button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="statistics">统计信息</button>
    </div>
</script>

<!-- 操作列模板 -->
<script type="text/html" id="actionBar">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>

<!-- 状态模板 -->
<script type="text/html" id="statusTpl">
    @{{# if(d.admin_name === '系统'){ }}
        <span class="layui-badge layui-bg-gray">系统</span>
    @{{# } else { }}
        <span class="layui-badge layui-bg-blue">@{{d.admin_name}}</span>
    @{{# } }}
</script>

<!-- 数据模板 -->
<script type="text/html" id="dataTpl">
    @{{# if(d.data && Object.keys(d.data).length > 0){ }}
        <button class="layui-btn layui-btn-xs layui-btn-normal" lay-event="viewData">查看数据</button>
    @{{# } else { }}
        <span class="layui-text-muted">无</span>
    @{{# } }}
</script>

<script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>
layui.use(['table', 'form', 'laydate', 'layer'], function(){
    var table = layui.table;
    var form = layui.form;
    var laydate = layui.laydate;
    var layer = layui.layer;
    var $ = layui.$;

    // 初始化日期范围选择器
    laydate.render({
        elem: '#dateRange',
        type: 'datetime',
        range: true,
        format: 'yyyy-MM-dd HH:mm:ss'
    });

    // 初始化数据表格
    var tableIns = table.render({
        elem: '#logTable',
        url: '{{ route("lpadmin.system-log.index") }}',
        toolbar: '#toolbarDemo',
        defaultToolbar: ['filter', 'exports', 'print'],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'id', title: 'ID', width: 80, sort: true},
            {field: 'admin_name', title: '操作人', width: 120, templet: '#statusTpl'},
            {field: 'action', title: '操作类型', width: 120},
            {field: 'description', title: '操作描述', width: 250, style: 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'},
            {field: 'ip', title: 'IP地址', width: 130},
            {field: 'data', title: '操作数据', width: 100, templet: '#dataTpl'},
            {field: 'created_at', title: '操作时间', width: 160, sort: true},
            {title: '操作', width: 120, toolbar: '#actionBar', fixed: 'right'}
        ]],
        page: true,
        height: 'full-200',
        cellMinWidth: 80,
        request: {
            pageName: 'page',
            limitName: 'limit'
        },
        parseData: function(res){
            return {
                "code": res.code,
                "msg": res.msg,
                "count": res.count,
                "data": res.data
            };
        }
    });

    // 搜索表单提交
    form.on('submit(search)', function(data){
        var field = data.field;
        
        // 处理时间范围
        if(field.created_at){
            var dates = field.created_at.split(' - ');
            field.created_at = dates;
        }
        
        tableIns.reload({
            where: field,
            page: {
                curr: 1
            }
        });
        return false;
    });

    // 工具栏事件
    table.on('toolbar(logTable)', function(obj){
        var checkStatus = table.checkStatus(obj.config.id);
        
        switch(obj.event){
            case 'batchDelete':
                var data = checkStatus.data;
                if(data.length === 0){
                    layer.msg('请选择要删除的数据');
                    return;
                }
                
                layer.confirm('确定删除选中的 ' + data.length + ' 条日志吗？', function(index){
                    var ids = data.map(function(item){
                        return item.id;
                    });
                    
                    $.post('{{ route("lpadmin.system-log.batch-delete") }}', {
                        ids: ids,
                        _token: '{{ csrf_token() }}'
                    }, function(res){
                        if(res.code === 0){
                            layer.msg(res.message, {icon: 1});
                            tableIns.reload();
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    });
                    
                    layer.close(index);
                });
                break;
                
            case 'statistics':
                showStatistics();
                break;
        }
    });

    // 行工具事件
    table.on('tool(logTable)', function(obj){
        var data = obj.data;
        
        switch(obj.event){
            case 'detail':
                showDetail(data.id);
                break;
                
            case 'del':
                layer.confirm('确定删除这条日志吗？', function(index){
                    $.ajax({
                        url: '/lpadmin/system-log/' + data.id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res){
                            if(res.code === 0){
                                layer.msg(res.message, {icon: 1});
                                tableIns.reload();
                            } else {
                                layer.msg(res.message, {icon: 2});
                            }
                        }
                    });
                    layer.close(index);
                });
                break;
                
            case 'viewData':
                if(data.data){
                    layer.open({
                        type: 1,
                        title: '操作数据',
                        area: ['600px', '400px'],
                        content: '<pre style="padding: 20px; max-height: 300px; overflow-y: auto;">' + 
                                JSON.stringify(data.data, null, 2) + '</pre>'
                    });
                }
                break;
        }
    });

    // 导出按钮事件
    $('#exportBtn').click(function(){
        var searchData = form.val('searchForm');
        
        // 处理时间范围
        if(searchData.created_at){
            var dates = searchData.created_at.split(' - ');
            searchData.created_at = dates;
        }
        
        // 创建表单并提交
        var form = $('<form method="post" action="{{ route("lpadmin.system-log.export") }}"></form>');
        form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');

        for(var key in searchData){
            if(searchData[key]){
                if(Array.isArray(searchData[key])){
                    searchData[key].forEach(function(val, index){
                        form.append('<input type="hidden" name="' + key + '[' + index + ']" value="' + val + '">');
                    });
                } else {
                    form.append('<input type="hidden" name="' + key + '" value="' + searchData[key] + '">');
                }
            }
        }
        
        $('body').append(form);
        form.submit();
        form.remove();
    });

    // 清空日志按钮事件
    $('#clearBtn').click(function(){
        layer.open({
            type: 1,
            title: '清空日志',
            area: ['400px', '300px'],
            content: '<div style="padding: 20px;">' +
                    '<div class="layui-form-item">' +
                    '<label class="layui-form-label">清空方式</label>' +
                    '<div class="layui-input-block">' +
                    '<input type="radio" name="clearType" value="all" title="清空所有日志" checked>' +
                    '<input type="radio" name="clearType" value="days" title="清空指定天数前的日志">' +
                    '</div>' +
                    '</div>' +
                    '<div class="layui-form-item" id="daysInput" style="display:none;">' +
                    '<label class="layui-form-label">保留天数</label>' +
                    '<div class="layui-input-block">' +
                    '<input type="number" name="days" value="30" class="layui-input">' +
                    '</div>' +
                    '</div>' +
                    '</div>',
            btn: ['确定', '取消'],
            yes: function(index, layero){
                var clearType = layero.find('input[name="clearType"]:checked').val();
                var days = clearType === 'days' ? layero.find('input[name="days"]').val() : 0;
                
                $.post('{{ route("lpadmin.system-log.clear") }}', {
                    days: days,
                    _token: '{{ csrf_token() }}'
                }, function(res){
                    if(res.code === 0){
                        layer.msg(res.message, {icon: 1});
                        tableIns.reload();
                    } else {
                        layer.msg(res.message, {icon: 2});
                    }
                });
                
                layer.close(index);
            }
        });
        
        // 监听单选框变化
        $(document).on('change', 'input[name="clearType"]', function(){
            if($(this).val() === 'days'){
                $('#daysInput').show();
            } else {
                $('#daysInput').hide();
            }
        });
    });

    // 显示详情
    function showDetail(id){
        $.get('/lpadmin/system-log/' + id, function(res){
            if(res.code === 0){
                var data = res.data;
                var content = '<div style="padding: 20px;">' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>ID:</strong></div><div class="layui-col-md9">' + data.id + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>操作人:</strong></div><div class="layui-col-md9">' + data.admin_name + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>操作类型:</strong></div><div class="layui-col-md9">' + data.action + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>操作描述:</strong></div><div class="layui-col-md9">' + data.description + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>IP地址:</strong></div><div class="layui-col-md9">' + data.ip + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>用户代理:</strong></div><div class="layui-col-md9">' + (data.user_agent || '无') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>操作时间:</strong></div><div class="layui-col-md9">' + data.created_at + '</div></div>';
                
                if(data.data && Object.keys(data.data).length > 0){
                    content += '<div class="layui-row"><div class="layui-col-md3"><strong>操作数据:</strong></div><div class="layui-col-md9"><pre>' + JSON.stringify(data.data, null, 2) + '</pre></div></div>';
                }
                
                content += '</div>';
                
                layer.open({
                    type: 1,
                    title: '日志详情',
                    area: ['700px', '500px'],
                    content: content
                });
            } else {
                layer.msg(res.message, {icon: 2});
            }
        });
    }

    // 显示统计信息
    function showStatistics(){
        $.get('{{ route("lpadmin.system-log.statistics") }}', function(res){
            if(res.code === 0){
                var stats = res.data;
                var content = '<div style="padding: 20px;">' +
                    '<div class="layui-row layui-col-space10">' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">总日志数</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#1E9FFF;">' + stats.total_logs + '</div></div></div>' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">今日日志</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#5FB878;">' + stats.today_logs + '</div></div></div>' +
                    '</div>' +
                    '<div class="layui-row layui-col-space10">' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">本周日志</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#FFB800;">' + stats.week_logs + '</div></div></div>' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">本月日志</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#FF5722;">' + stats.month_logs + '</div></div></div>' +
                    '</div>' +
                    '</div>';
                
                layer.open({
                    type: 1,
                    title: '统计信息',
                    area: ['600px', '400px'],
                    content: content
                });
            } else {
                layer.msg(res.message, {icon: 2});
            }
        });
    }
});
</script>
</body>
</html>
