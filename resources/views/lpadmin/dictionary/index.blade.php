<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>数据字典管理</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
        <link rel="stylesheet" href="/static/admin/css/table-common.css" />
    </head>
    <body class="pear-container">

        <!-- 顶部查询表单 -->
        <div class="layui-card">
            <div class="layui-card-body">
                <form class="layui-form top-search-from">

                    <div class="layui-form-item">
                        <label class="layui-form-label">字典名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">字典标题</label>
                        <div class="layui-input-block">
                            <input type="text" name="title" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">字典类型</label>
                        <div class="layui-input-block">
                            <select name="type">
                                <option value="">请选择类型</option>
                                <option value="select">下拉选择</option>
                                <option value="radio">单选框</option>
                                <option value="checkbox">复选框</option>
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
                <table id="data-table" lay-filter="data-table"></table>
            </div>
        </div>

        <!-- 表格顶部工具栏 -->
        <script type="text/html" id="table-toolbar">
            <button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add">
                <i class="layui-icon layui-icon-add-1"></i>新增
            </button>
            <button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove">
                <i class="layui-icon layui-icon-delete"></i>删除
            </button>
        </script>

        <!-- 表格行工具栏 -->
        <script type="text/html" id="table-bar">
            <div style="white-space: nowrap; display: flex; gap: 4px; justify-content: center;">
                <button class="table-action-btn table-action-view" lay-event="items" title="字典项">
                    <i class="layui-icon layui-icon-list"></i>
                </button>
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
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.dictionary.index') }}";
            const UPDATE_API = "{{ route('lpadmin.dictionary.update', ':id') }}";
            const DELETE_API = "{{ route('lpadmin.dictionary.destroy', ':id') }}";
            const STATUS_API = "{{ route('lpadmin.dictionary.toggle_status', ':id') }}";
            const INSERT_URL = "{{ route('lpadmin.dictionary.create') }}";
            const UPDATE_URL = "{{ route('lpadmin.dictionary.edit', ':id') }}";
            const ITEMS_URL = "{{ route('lpadmin.dictionary.items.index', ':id') }}";
            const BATCH_DELETE_API = "{{ route('lpadmin.dictionary.batch_destroy') }}";

            // 表格渲染
            layui.use(["table", "form", "layer", "util"], function() {
                let table = layui.table;
                let form = layui.form;
                let layer = layui.layer;
                let $ = layui.$;
                let util = layui.util;

				// 表头参数
				let cols = [
					{
						type: "checkbox"
					},{
						title: "ID",
						field: "id",
                        width: 80,
                        sort: true,
					},{
						title: "字典名称",
						field: "name",
                        width: 150,
					},{
						title: "字典标题",
						field: "title",
                        width: 200,
					},{
						title: "字典类型",
						field: "type_label",
                        width: 100,
                        templet: function (d) {
                            let colors = {
                                'select': '#1890ff',
                                'radio': '#52c41a', 
                                'checkbox': '#fa8c16'
                            };
                            let color = colors[d.type] || '#666';
                            return '<span style="color: ' + color + ';">' + util.escape(d.type_label) + '</span>';
                        }
					},{
						title: "描述",
						field: "description",
                        hide: true,
					},{
						title: "字典项数",
						field: "items_count",
                        width: 100,
                        align: 'center',
					},{
						title: "排序",
						field: "sort",
                        width: 80,
                        align: 'center',
					},{
                        title: "状态",
                        field: "status",
                        width: 100,
                        templet: function (d) {
                            let field = "status";
                            form.on("switch("+field+")", function (data) {
                                let load = layer.load();
                                let postData = {};
                                postData[field] = data.elem.checked ? 1 : 0;
                                postData['_token'] = $('meta[name="csrf-token"]').attr('content');
                                let url = STATUS_API.replace(':id', this.value);
                                $.post(url, postData, function (res) {
                                    layer.close(load);
                                    if (res.code !== 0) {
                                        layer.msg(res.message, {icon: 2});
                                        data.elem.checked = !data.elem.checked;
                                        form.render();
                                        return;
                                    }
                                    layer.msg(res.message, {icon: 1});
                                });
                            });
                            let checked = d[field] == 1 ? "checked" : "";
                            return '<input type="checkbox" name="'+field+'" value="'+d[PRIMARY_KEY]+'" lay-skin="switch" lay-text="启用|禁用" lay-filter="'+field+'" '+checked+' />';
                        }
                    },{
						title: "创建时间",
						field: "created_at",
                        width: 160,
                        hide: true,
					},{
						title: "更新时间",
						field: "updated_at",
                        width: 160,
                        hide: true,
					},{
						title: "操作",
						toolbar: "#table-bar",
						align: "center",
						width: 130,
                        fixed: "right"
					}
				];

				// 表格配置
				table.render({
					elem: "#data-table",
					url: SELECT_API,
					toolbar: "#table-toolbar",
					defaultToolbar: [{
                        title: '刷新',
                        layEvent: 'refresh',
                        icon: 'layui-icon-refresh',
                    }, "filter", "exports", "print"],
					cols: [cols],
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
					}
				});

                // 搜索
                form.on("submit(search)", function (data) {
                    table.reload("data-table", {
                        where: data.field,
                        page: {
                            curr: 1
                        }
                    });
                    return false;
                });

                // 头工具栏事件
                table.on("toolbar(data-table)", function (obj) {
                    if (obj.event === "add") {
                        layer.open({
                            type: 2,
                            title: "新增字典",
                            shade: 0.1,
                            area: ["800px", "600px"],
                            content: INSERT_URL
                        });
                    } else if (obj.event === "batchRemove") {
                        let checkStatus = table.checkStatus("data-table");
                        let data = checkStatus.data;
                        if (data.length === 0) {
                            return layer.msg("请选择要删除的数据", {icon: 2});
                        }
                        let ids = data.map(item => item[PRIMARY_KEY]);
                        layer.confirm("确定要删除选中的数据吗？", {
                            icon: 3,
                            title: "提示"
                        }, function(index) {
                            let load = layer.load();
                            $.post(BATCH_DELETE_API, {
                                ids: ids,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }, function (res) {
                                layer.close(load);
                                layer.close(index);
                                if (res.code === 0) {
                                    layer.msg(res.message, {icon: 1});
                                    table.reload("data-table");
                                } else {
                                    layer.msg(res.message, {icon: 2});
                                }
                            });
                        });
                    }else if (obj.event === 'refresh') {
                        table.reload("data-table");
                    }
                });

                // 行工具栏事件
                table.on("tool(data-table)", function (obj) {
                    if (obj.event === "edit") {
                        let url = UPDATE_URL.replace(':id', obj.data[PRIMARY_KEY]);
                        layer.open({
                            type: 2,
                            title: "编辑字典",
                            shade: 0.1,
                            area: ["800px", "600px"],
                            content: url
                        });
                    } else if (obj.event === "remove") {
                        layer.confirm("确定要删除这条数据吗？", {
                            icon: 3,
                            title: "提示"
                        }, function(index) {
                            let load = layer.load();
                            let url = DELETE_API.replace(':id', obj.data[PRIMARY_KEY]);
                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (res) {
                                    layer.close(load);
                                    layer.close(index);
                                    if (res.code === 0) {
                                        layer.msg(res.message, {icon: 1});
                                        table.reload("data-table");
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                }
                            });
                        });
                    } else if (obj.event === "items") {
                        let url = ITEMS_URL.replace(':id', obj.data[PRIMARY_KEY]);
                        layer.open({
                            type: 2,
                            title: "字典项管理 - " + obj.data.title,
                            shade: 0.1,
                            area: ["1200px", "700px"],
                            content: url
                        });
                    }
                });
            });
        </script>
    </body>
</html>
