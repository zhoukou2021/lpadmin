<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>新增角色</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form" action="">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">角色名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="name" value="" required lay-verify="required" class="layui-input" placeholder="请输入角色名称">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">显示名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="display_name" value="" required lay-verify="required" class="layui-input" placeholder="请输入显示名称">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">描述</label>
                        <div class="layui-input-block">
                            <textarea name="description" class="layui-textarea" placeholder="请输入角色描述"></textarea>
                        </div>
                    </div>

                    {{-- 使用通用单选框组件 --}}
                    @include('lpadmin.components.radio-group', [
                        'type' => 'status',
                        'name' => 'status',
                        'label' => '状态',
                        'required' => false,
                        'default' => '1'
                    ])

                    <div class="layui-form-item">
                        <label class="layui-form-label">排序</label>
                        <div class="layui-input-block">
                            <input type="number" name="sort" value="0" class="layui-input" placeholder="数字越小排序越靠前">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-sm" lay-submit lay-filter="save">
                        <i class="layui-icon layui-icon-ok"></i>
                        提交
                    </button>
                    <button type="reset" class="pear-btn pear-btn-sm">
                        <i class="layui-icon layui-icon-refresh"></i>
                        重置
                    </button>
                </div>
            </div>

        </form>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const INSERT_API = "{{ route('lpadmin.role.store') }}";

            //提交事件
            layui.use(["form", "popup"], function () {
                // 初始化单选框
                if (window.RadioHelper) {
                    RadioHelper.init('status');
                }

                layui.form.on("submit(save)", function (data) {
                    // 修复单选框数据
                    if (window.RadioHelper) {
                        RadioHelper.fixFormData(data.field, ['status']);
                    }
                    // 添加CSRF token
                    data.field._token = '{{ csrf_token() }}';

                    layui.$.ajax({
                        url: INSERT_API,
                        type: "POST",
                        dateType: "json",
                        data: data.field,
                        success: function (res) {
                            if (res.code !== 0) {
                                return layui.popup.failure(res.message);
                            }
                            return layui.popup.success("操作成功", function () {
                                parent.refreshTable();
                                parent.layer.close(parent.layer.getFrameIndex(window.name));
                            });
                        }
                    });
                    return false;
                });
            });

        </script>

    </body>

</html>
