<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>组件管理</title>
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
                        <span class="layui-icon layui-icon-component"></span>
                        组件管理
                    </div>
                    <div class="layui-card-body">
                    <!-- 搜索表单 -->
                    <form class="layui-form" lay-filter="searchForm">
                        <div class="layui-row layui-col-space10">
                            <div class="layui-col-md3">
                                <input type="text" name="name" placeholder="组件名称" class="layui-input">
                            </div>
                            <div class="layui-col-md3">
                                <select name="status">
                                    <option value="">全部状态</option>
                                    <option value="1">已安装</option>
                                    <option value="0">未安装</option>
                                </select>
                            </div>
                            <div class="layui-col-md3">
                                <input type="text" name="author" placeholder="作者" class="layui-input">
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
                    <table class="layui-hide" id="componentTable" lay-filter="componentTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 工具栏模板 -->
<script type="text/html" id="toolbarDemo">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="refresh">
            <i class="layui-icon layui-icon-refresh"></i> 刷新组件
        </button>
        <button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="batchInstall">批量安装</button>
        <button class="layui-btn layui-btn-warm layui-btn-sm" lay-event="batchUninstall">批量卸载</button>
        <button class="layui-btn layui-btn-primary layui-btn-sm" lay-event="statistics">统计信息</button>
    </div>
</script>

<!-- 操作列模板 -->
<script type="text/html" id="actionBar">
    @{{# if(d.status === 1){ }}
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="uninstall">卸载</a>
    @{{# } else { }}
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="install">安装</a>
    @{{# } }}
    <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">详情</a>
    <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="validate">验证</a>
</script>

<!-- 状态模板 -->
<script type="text/html" id="statusTpl">
    @{{# if(d.status === 1){ }}
        <span class="layui-badge layui-bg-green">已安装</span>
    @{{# } else { }}
        <span class="layui-badge layui-bg-gray">未安装</span>
    @{{# } }}
</script>

<!-- 完整性模板 -->
<script type="text/html" id="completeTpl">
    @{{# if(d.is_complete){ }}
        <span class="layui-badge layui-bg-blue">完整</span>
    @{{# } else { }}
        <span class="layui-badge layui-bg-orange">不完整</span>
    @{{# } }}
</script>

<!-- 文件结构模板 -->
<script type="text/html" id="filesTpl">
    <div>
        @{{# if(d.has_controller){ }}
            <span class="layui-badge layui-bg-green">控制器</span>
        @{{# } }}
        @{{# if(d.has_routes){ }}
            <span class="layui-badge layui-bg-blue">路由</span>
        @{{# } }}
        @{{# if(d.has_views){ }}
            <span class="layui-badge layui-bg-cyan">视图</span>
        @{{# } }}
    </div>
</script>

<script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>
layui.use(['table', 'form', 'layer'], function(){
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var $ = layui.$;

    // 初始化数据表格
    var tableIns = table.render({
        elem: '#componentTable',
        url: '{{ route("lpadmin.component.index") }}',
        toolbar: '#toolbarDemo',
        defaultToolbar: ['filter', 'exports', 'print'],
        cols: [[
            {type: 'checkbox', fixed: 'left'},
            {field: 'name', title: '组件名称', width: 150, sort: true},
            {field: 'title', title: '组件标题', width: 200},
            {field: 'description', title: '描述', minWidth: 250},
            {field: 'version', title: '版本', width: 100},
            {field: 'author', title: '作者', width: 120},
            {field: 'status', title: '状态', width: 100, templet: '#statusTpl'},
            {field: 'is_complete', title: '完整性', width: 100, templet: '#completeTpl'},
            {field: 'files', title: '文件结构', width: 200, templet: '#filesTpl'},
            {field: 'installed_at', title: '安装时间', width: 160},
            {title: '操作', width: 200, toolbar: '#actionBar', fixed: 'right'}
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
        tableIns.reload({
            where: data.field,
            page: {
                curr: 1
            }
        });
        return false;
    });

    // 工具栏事件
    table.on('toolbar(componentTable)', function(obj){
        var checkStatus = table.checkStatus(obj.config.id);
        
        switch(obj.event){
            case 'refresh':
                refreshComponents();
                break;
                
            case 'batchInstall':
                var data = checkStatus.data;
                if(data.length === 0){
                    layer.msg('请选择要安装的组件');
                    return;
                }
                
                var uninstalledComponents = data.filter(function(item){
                    return item.status === 0;
                });
                
                if(uninstalledComponents.length === 0){
                    layer.msg('选中的组件都已安装');
                    return;
                }
                
                batchAction('install', uninstalledComponents);
                break;
                
            case 'batchUninstall':
                var data = checkStatus.data;
                if(data.length === 0){
                    layer.msg('请选择要卸载的组件');
                    return;
                }
                
                var installedComponents = data.filter(function(item){
                    return item.status === 1;
                });
                
                if(installedComponents.length === 0){
                    layer.msg('选中的组件都未安装');
                    return;
                }
                
                batchAction('uninstall', installedComponents);
                break;
                
            case 'statistics':
                showStatistics();
                break;
        }
    });

    // 行工具事件
    table.on('tool(componentTable)', function(obj){
        var data = obj.data;
        
        switch(obj.event){
            case 'install':
                installComponent(data.name);
                break;
                
            case 'uninstall':
                uninstallComponent(data.name);
                break;
                
            case 'detail':
                showDetail(data.name);
                break;
                
            case 'validate':
                validateComponent(data.name);
                break;
        }
    });

    // 安装组件
    function installComponent(name){
        layer.confirm('确定安装组件 "' + name + '" 吗？', function(index){
            var loading = layer.load(2);
            
            $.post('{{ route("lpadmin.component.install") }}', {
                name: name,
                _token: '{{ csrf_token() }}'
            }, function(res){
                layer.close(loading);
                if(res.code === 0){
                    layer.msg(res.message, {icon: 1});
                    tableIns.reload();
                } else {
                    layer.msg(res.message, {icon: 2});
                }
            });
            
            layer.close(index);
        });
    }

    // 卸载组件
    function uninstallComponent(name){
        layer.confirm('确定卸载组件 "' + name + '" 吗？<br><span style="color:red;">注意：卸载后相关数据可能会丢失！</span>', function(index){
            var loading = layer.load(2);
            
            $.post('{{ route("lpadmin.component.uninstall") }}', {
                name: name,
                _token: '{{ csrf_token() }}'
            }, function(res){
                layer.close(loading);
                if(res.code === 0){
                    layer.msg(res.message, {icon: 1});
                    tableIns.reload();
                } else {
                    layer.msg(res.message, {icon: 2});
                }
            });
            
            layer.close(index);
        });
    }

    // 批量操作
    function batchAction(action, components){
        var actionText = action === 'install' ? '安装' : '卸载';
        var componentNames = components.map(function(item){
            return item.name;
        });
        
        layer.confirm('确定' + actionText + '选中的 ' + components.length + ' 个组件吗？', function(index){
            var loading = layer.load(2);
            
            $.post('{{ route("lpadmin.component.batch_action") }}', {
                action: action,
                components: componentNames,
                _token: '{{ csrf_token() }}'
            }, function(res){
                layer.close(loading);
                if(res.code === 0){
                    layer.msg(res.message, {icon: 1});
                    tableIns.reload();
                } else {
                    layer.msg(res.message, {icon: 2});
                }
            });
            
            layer.close(index);
        });
    }

    // 刷新组件
    function refreshComponents(){
        var loading = layer.load(2);
        
        $.post('{{ route("lpadmin.component.refresh") }}', {
            _token: '{{ csrf_token() }}'
        }, function(res){
            layer.close(loading);
            if(res.code === 0){
                layer.msg(res.message, {icon: 1});
                tableIns.reload();
            } else {
                layer.msg(res.message, {icon: 2});
            }
        });
    }

    // 显示详情
    function showDetail(name){
        $.get('{{ route("lpadmin.component.show", ":name") }}'.replace(':name', name), function(res){
            if(res.code === 0){
                var data = res.data;
                var content = '<div style="padding: 20px;">' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>组件名称:</strong></div><div class="layui-col-md9">' + data.name + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>组件标题:</strong></div><div class="layui-col-md9">' + (data.title || '无') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>描述:</strong></div><div class="layui-col-md9">' + (data.description || '无') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>版本:</strong></div><div class="layui-col-md9">' + (data.version || '1.0.0') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>作者:</strong></div><div class="layui-col-md9">' + (data.author || '无') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>状态:</strong></div><div class="layui-col-md9">' + (data.status === 1 ? '已安装' : '未安装') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>安装时间:</strong></div><div class="layui-col-md9">' + (data.installed_at || '未安装') + '</div></div>' +
                    '<div class="layui-row"><div class="layui-col-md3"><strong>文件结构:</strong></div><div class="layui-col-md9">' +
                    (data.has_controller ? '<span class="layui-badge layui-bg-green">控制器</span> ' : '') +
                    (data.has_routes ? '<span class="layui-badge layui-bg-blue">路由</span> ' : '') +
                    (data.has_views ? '<span class="layui-badge layui-bg-cyan">视图</span> ' : '') +
                    '</div></div>' +
                    '</div>';
                
                layer.open({
                    type: 1,
                    title: '组件详情',
                    area: ['600px', '400px'],
                    content: content
                });
            } else {
                layer.msg(res.message, {icon: 2});
            }
        });
    }

    // 验证组件
    function validateComponent(name){
        $.get('{{ route("lpadmin.component.validate", ":name") }}'.replace(':name', name), function(res){
            if(res.code === 0){
                var validation = res.data;
                var content = '<div style="padding: 20px;">';
                
                if(validation.valid){
                    content += '<div style="color: green; margin-bottom: 10px;"><i class="layui-icon layui-icon-ok"></i> 组件验证通过</div>';
                } else {
                    content += '<div style="color: red; margin-bottom: 10px;"><i class="layui-icon layui-icon-close"></i> 组件验证失败</div>';
                }
                
                if(validation.errors && validation.errors.length > 0){
                    content += '<div><strong>错误:</strong><ul>';
                    validation.errors.forEach(function(error){
                        content += '<li style="color: red;">' + error + '</li>';
                    });
                    content += '</ul></div>';
                }
                
                if(validation.warnings && validation.warnings.length > 0){
                    content += '<div><strong>警告:</strong><ul>';
                    validation.warnings.forEach(function(warning){
                        content += '<li style="color: orange;">' + warning + '</li>';
                    });
                    content += '</ul></div>';
                }
                
                content += '</div>';
                
                layer.open({
                    type: 1,
                    title: '组件验证结果',
                    area: ['500px', '400px'],
                    content: content
                });
            } else {
                layer.msg(res.message, {icon: 2});
            }
        });
    }

    // 显示统计信息
    function showStatistics(){
        $.get('{{ route("lpadmin.component.statistics") }}', function(res){
            if(res.code === 0){
                var stats = res.data;
                var content = '<div style="padding: 20px;">' +
                    '<div class="layui-row layui-col-space10">' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">总组件数</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#1E9FFF;">' + stats.total_components + '</div></div></div>' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">已安装</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#5FB878;">' + stats.installed_components + '</div></div></div>' +
                    '</div>' +
                    '<div class="layui-row layui-col-space10">' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">未安装</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#FFB800;">' + stats.uninstalled_components + '</div></div></div>' +
                    '<div class="layui-col-md6"><div class="layui-card"><div class="layui-card-header">完整组件</div><div class="layui-card-body" style="text-align:center;font-size:24px;color:#FF5722;">' + stats.complete_components + '</div></div></div>' +
                    '</div>' +
                    '</div>';
                
                layer.open({
                    type: 1,
                    title: '组件统计',
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
