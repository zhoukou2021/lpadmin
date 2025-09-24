<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>修改密码</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">原密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="old_password" value="" required lay-verify="required|minLength" minlength="6" class="layui-input" placeholder="请输入原密码">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">新密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password" value="" required lay-verify="required|minLength" minlength="6" class="layui-input" placeholder="请输入新密码，至少6位">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">确认密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password_confirmation" value="" required lay-verify="required|minLength" minlength="6" class="layui-input" placeholder="请再次输入新密码">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
                        修改密码
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
                </div>
            </div>

        </form>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script>

            // 相关接口
            const UPDATE_API = "{{ route('lpadmin.change_password.update') }}";

            //提交事件
            layui.use(["form", "popup"], function () {
                // 自定义验证规则
                layui.form.verify({
                    minLength: function(value, item) {
                        var minLength = parseInt(item.getAttribute('minlength')) || 6;
                        if (value.length < minLength) {
                            return '密码至少' + minLength + '位';
                        }
                    }
                });
                layui.form.on("submit(save)", function (data) {
                    // 验证两次密码是否一致
                    if (data.field.password !== data.field.password_confirmation) {
                        return layui.popup.failure("两次密码输入不一致");
                    }

                    // 验证密码长度
                    if (data.field.password.length < 6) {
                        return layui.popup.failure("新密码至少6位");
                    }

                    // 添加CSRF token
                    data.field._token = '{{ csrf_token() }}';

                    layui.$.ajax({
                        url: UPDATE_API,
                        type: "POST",
                        dateType: "json",
                        data: data.field,
                        success: function (res) {
                            if (res.code !== 0) {
                                return layui.popup.failure(res.message);
                            }
                            return layui.popup.success("密码修改成功", function () {
                                // 关闭弹窗
                                if (parent && parent.layer) {
                                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                                }
                            });
                        }
                    });
                    return false;
                });
            });

        </script>

    </body>

</html>
