<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>分配权限 - {{ $role->name }}</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label">角色信息</label>
                        <div class="layui-input-block">
                            <div class="layui-text">
                                <strong>{{ $role->name }}</strong> ({{ $role->display_name }})
                                @if($role->description)
                                    <br><small class="layui-text-muted">{{ $role->description }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">权限分配</label>
                        <div class="layui-input-block">
                            <div class="layui-btn-group">
                                <button type="button" class="layui-btn layui-btn-sm" id="checkAll">全选</button>
                                <button type="button" class="layui-btn layui-btn-sm" id="uncheckAll">全不选</button>
                                <button type="button" class="layui-btn layui-btn-sm" id="expandAll">展开全部</button>
                                <button type="button" class="layui-btn layui-btn-sm" id="collapseAll">收起全部</button>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <div id="permission-tree"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-sm" lay-submit lay-filter="save">
                        <i class="layui-icon layui-icon-ok"></i>
                        保存权限
                    </button>
                    <button type="button" class="pear-btn pear-btn-sm" onclick="parent.layer.closeAll()">
                        <i class="layui-icon layui-icon-close"></i>
                        取消
                    </button>
                </div>
            </div>

        </form>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script>

            // 相关接口
            const PERMISSION_API = "{{ route('lpadmin.role.update_permissions', $role->id) }}";
            const RULES_API = "{{ route('lpadmin.rule.permission_tree') }}";

            layui.use(['form', 'dtree', 'popup'], function() {
                var form = layui.form;
                var dtree = layui.dtree;
                var $ = layui.$;

                // 渲染权限树
                var DTree = dtree.render({
                    elem: "#permission-tree",
                    url: RULES_API,
                    method: "GET",
                    dataStyle: "layuiStyle",
                    response: {message: "msg", statusCode: 0},
                    checkbar: true,
                    checkbarType: "p-casc",  // 使用p-casc模式，支持父子节点独立选择
                    checkbarData: "choose",
                    icon: "2",
                    accordion: false,
                    initLevel: 2,
                    done: function(res, $ul, first) {
                        // 设置已选中的权限
                        var checkedIds = @json($role->rules->pluck('id')->toArray());
                        if (checkedIds.length > 0) {
                            dtree.chooseDataInit("permission-tree", checkedIds);
                        }
                    }
                });

                // 全选
                $('#checkAll').click(function() {
                    DTree.checkAllNode();
                });

                // 全不选
                $('#uncheckAll').click(function() {
                    DTree.cancelCheckedNode();
                });

                // 展开全部
                $('#expandAll').click(function() {
                    DTree.menubarMethod().openAllNode();
                });

                // 收起全部
                $('#collapseAll').click(function() {
                    DTree.menubarMethod().closeAllNode();
                });

                // 提交表单
                form.on('submit(save)', function(data) {
                    // 获取选中的权限ID
                    var checkedData = dtree.getCheckbarNodesParam("permission-tree");
                    var ruleIds = [];

                    if (checkedData && checkedData.length > 0) {
                        checkedData.forEach(function(item) {
                            ruleIds.push(item.nodeId);
                        });
                    }

                    $.ajax({
                        url: PERMISSION_API,
                        type: 'POST',
                        data: {
                            rule_ids: ruleIds,
                            _token: '{{ csrf_token() }}'
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.code === 0) {
                                layui.popup.success(res.message, function() {
                                    parent.refreshTable();
                                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                                });
                            } else {
                                layui.popup.failure(res.message);
                            }
                        },
                        error: function() {
                            layui.popup.failure('网络错误，请稍后重试');
                        }
                    });

                    return false;
                });
            });

        </script>

    </body>

</html>
