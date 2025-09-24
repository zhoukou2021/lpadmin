<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>字典项管理 - {{ $dictionary->title }}</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    <style>
        .dict-item-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 5px;
            vertical-align: middle;
        }
        .color-blue { background-color: #1890ff; }
        .color-green { background-color: #52c41a; }
        .color-orange { background-color: #fa8c16; }
        .color-red { background-color: #ff4d4f; }
        .color-purple { background-color: #722ed1; }
        .color-cyan { background-color: #13c2c2; }
        .color-gray { background-color: #8c8c8c; }
        .color-pink { background-color: #eb2f96; }
    </style>
</head>
<body class="pear-container">
    <!-- 字典信息卡片 -->
    <div class="layui-card" style="margin-bottom: 15px;">
        <div class="layui-card-body">
            <div class="layui-row">
                <div class="layui-col-md8">
                    <h3 style="margin: 0;">{{ $dictionary->title }} ({{ $dictionary->name }})</h3>
                    <p style="margin: 5px 0; color: #666;">{{ $dictionary->description ?: '暂无描述' }}</p>
                </div>
                <div class="layui-col-md4" style="text-align: right;">
                    <span class="layui-badge layui-bg-blue">{{ $dictionary->type_label }}</span>
                    <span class="layui-badge {{ $dictionary->status ? 'layui-bg-green' : 'layui-bg-gray' }}">
                        {{ $dictionary->status ? '启用' : '禁用' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 顶部查询表单 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form top-search-from">
                <div class="layui-form-item">
                    <label class="layui-form-label">显示标签</label>
                    <div class="layui-input-block">
                        <input type="text" name="label" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">选项值</label>
                    <div class="layui-input-block">
                        <input type="text" name="value" value="" class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">颜色</label>
                    <div class="layui-input-block">
                        <select name="color">
                            <option value="">请选择颜色</option>
                            <option value="blue">蓝色</option>
                            <option value="green">绿色</option>
                            <option value="orange">橙色</option>
                            <option value="red">红色</option>
                            <option value="purple">紫色</option>
                            <option value="cyan">青色</option>
                            <option value="gray">灰色</option>
                            <option value="pink">粉色</option>
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-block">
                        <select name="status">
                            <option value="">请选择状态</option>
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <button type="submit" class="layui-btn layui-btn-sm" lay-submit lay-filter="search">
                        <i class="layui-icon layui-icon-search"></i> 搜索
                    </button>
                    <button type="reset" class="layui-btn layui-btn-sm layui-btn-primary">
                        <i class="layui-icon layui-icon-refresh"></i> 重置
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 数据表格 -->
    <div class="layui-card">
        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
        </div>
    </div>

    <!-- 表格工具栏 -->
    <script type="text/html" id="toolbarDemo">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-sm" lay-event="add">
                <i class="layui-icon layui-icon-add-1"></i> 新增字典项
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="batchDelete">
                <i class="layui-icon layui-icon-delete"></i> 批量删除
            </button>
            <button class="layui-btn layui-btn-sm layui-btn-warm" lay-event="sort">
                <i class="layui-icon layui-icon-up-down"></i> 排序模式
            </button>
        </div>
    </script>

    <!-- 表格行工具栏 -->
    <script type="text/html" id="barDemo">
        <div style="white-space: nowrap; display: flex; gap: 4px; justify-content: center;">
            <button class="table-action-btn table-action-edit" lay-event="edit" title="编辑">
                <i class="layui-icon layui-icon-edit"></i>
            </button>
            <button class="table-action-btn table-action-delete" lay-event="delete" title="删除">
                <i class="layui-icon layui-icon-delete"></i>
            </button>
        </div>
    </script>

    <script src="/static/admin/component/layui/layui.js"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        // 相关常量
        const PRIMARY_KEY = "id";
        const DICTIONARY_ID = "{{ $dictionary->id }}";
        const SELECT_API = "{{ route('lpadmin.dictionary.items.select', $dictionary->id) }}";
        const UPDATE_API = "{{ route('lpadmin.dictionary.items.update', ['dictionary' => $dictionary->id, 'item' => ':id']) }}";
        const DELETE_API = "{{ route('lpadmin.dictionary.items.destroy', ['dictionary' => $dictionary->id, 'item' => ':id']) }}";
        const STATUS_API = "{{ route('lpadmin.dictionary.items.toggle_status', ['dictionary' => $dictionary->id, 'item' => ':id']) }}";
        const INSERT_URL = "{{ route('lpadmin.dictionary.items.create', $dictionary->id) }}";
        const UPDATE_URL = "{{ route('lpadmin.dictionary.items.edit', ['dictionary' => $dictionary->id, 'item' => ':id']) }}";
        const BATCH_DELETE_API = "{{ route('lpadmin.dictionary.items.batch_destroy', $dictionary->id) }}";

        layui.use(['table', 'form', 'layer', 'util'], function(){
            var table = layui.table;
            var form = layui.form;
            var layer = layui.layer;
            var util = layui.util;
            var $ = layui.$;

            var sortMode = false;

            // 数据表格
            var dataTable = table.render({
                elem: '#dataTable',
                url: SELECT_API,
                toolbar: '#toolbarDemo',
                defaultToolbar: [],
                page: true,
                limit: 15,
                limits: [15, 30, 50, 100],
                parseData: function (res) {
                    return {
                        "code": res.code,
                        "msg": res.msg,
                        "count": res.count,
                        "data": res.data
                    };
                },
                request: {
                    pageName: "page",
                    limitName: "limit"
                },
                cols: [[
                    {type: 'checkbox', fixed: 'left', width: 50},
                    {field: 'id', title: 'ID', width: 80, sort: true, fixed: 'left'},
                    {field: 'label', title: '显示标签', width: 150},
                    {field: 'value', title: '选项值', width: 150},
                    {field: 'color', title: '颜色', width: 100, templet: function(d){
                        if(!d.color) return '-';
                        var colorNames = {
                            'blue': '蓝色',
                            'green': '绿色',
                            'orange': '橙色',
                            'red': '红色',
                            'purple': '紫色',
                            'cyan': '青色',
                            'gray': '灰色',
                            'pink': '粉色'
                        };
                        var colorName = colorNames[d.color] || d.color;
                        return '<span class="dict-item-color color-' + d.color + '"></span>' + colorName;
                    }},
                    {field: 'description', title: '描述', width: 200},
                    {field: 'sort', title: '排序', width: 80, align: 'center'},
                    {field: 'status', title: '状态', width: 100, templet: function(d){
                        var checked = d.status == 1 ? 'checked' : '';
                        return '<input type="checkbox" name="status" value="'+d.id+'" lay-skin="switch" lay-text="启用|禁用" '+checked+' lay-filter="statusSwitch">';
                    }},
                    {field: 'created_at', title: '创建时间', width: 160},
                    {title: '操作', width: 120, align: 'center', toolbar: '#barDemo', fixed: 'right'}
                ]],
                request: {
                    pageName: 'page',
                    limitName: 'limit'
                },
                response: {
                    statusName: 'code',
                    statusCode: 0,
                    msgName: 'message',
                    countName: 'total',
                    dataName: 'data'
                }
            });

            // 搜索
            form.on('submit(search)', function(data){
                dataTable.reload({
                    where: data.field,
                    page: {curr: 1}
                });
                return false;
            });

            // 状态切换
            form.on('switch(statusSwitch)', function(data){
                var id = data.value;
                var status = data.elem.checked ? 1 : 0;

                $.ajax({
                    url: STATUS_API.replace(':id', id),
                    type: 'POST',
                    data: {
                        status: status,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res){
                        if(res.code === 0){
                            layer.msg(res.message, {icon: 1});
                        } else {
                            layer.msg(res.message, {icon: 2});
                            data.elem.checked = !data.elem.checked;
                            form.render('checkbox');
                        }
                    }
                });
            });

            // 头工具栏事件
            table.on('toolbar(dataTable)', function(obj){
                switch(obj.event){
                    case 'add':
                        layer.open({
                            type: 2,
                            title: '新增字典项',
                            area: ['800px', '600px'],
                            content: INSERT_URL,
                            end: function(){
                                dataTable.reload();
                            }
                        });
                        break;
                    case 'batchDelete':
                        var checkStatus = table.checkStatus('dataTable');
                        if(checkStatus.data.length === 0){
                            layer.msg('请选择要删除的数据');
                            return;
                        }

                        layer.confirm('确定删除选中的数据吗？', function(index){
                            var ids = [];
                            layui.each(checkStatus.data, function(i, item){
                                ids.push(item.id);
                            });

                            $.ajax({
                                url: BATCH_DELETE_API,
                                type: 'POST',
                                data: {
                                    ids: ids,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res){
                                    if(res.code === 0){
                                        layer.msg(res.message, {icon: 1});
                                        dataTable.reload();
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                }
                            });
                            layer.close(index);
                        });
                        break;
                    case 'sort':
                        sortMode = !sortMode;
                        if(sortMode){
                            layer.msg('排序模式已开启，拖拽行进行排序', {icon: 1});
                            // 这里可以添加拖拽排序功能
                        } else {
                            layer.msg('排序模式已关闭', {icon: 1});
                        }
                        break;
                }
            });

            // 行工具栏事件
            table.on('tool(dataTable)', function(obj){
                var data = obj.data;

                if(obj.event === 'edit'){
                    // 编辑字典项
                    layer.open({
                        type: 2,
                        title: '编辑字典项',
                        area: ['800px', '600px'],
                        content: UPDATE_URL.replace(':id', data.id),
                        end: function(){
                            dataTable.reload();
                        }
                    });
                } else if(obj.event === 'delete'){
                    // 删除字典项
                    layer.confirm('确定删除该字典项吗？', function(index){
                        $.ajax({
                            url: DELETE_API.replace(':id', data.id),
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res){
                                if(res.code === 0){
                                    layer.msg(res.message, {icon: 1});
                                    obj.del();
                                } else {
                                    layer.msg(res.message, {icon: 2});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }
            });
        });
    </script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</body>
</html>
