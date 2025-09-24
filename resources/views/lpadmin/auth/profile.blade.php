<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>个人资料</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label">用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="username" value="{{ $admin->username }}" readonly class="layui-input layui-disabled">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">昵称</label>
                        <div class="layui-input-block">
                            <input type="text" name="nickname" value="{{ $admin->nickname }}" required lay-verify="required" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">头像</label>
                        <div class="layui-input-block">
                            <div class="avatar-container">
                                <img class="avatar-preview" src="{{ $admin->avatar_url }}" alt="用户头像"/>
                                <input type="text" style="display:none" name="avatar" value="{{ $admin->avatar }}" />
                                <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" id="avatar">
                                    <i class="layui-icon layui-icon-upload"></i>上传图片
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">邮箱</label>
                        <div class="layui-input-block">
                            <input type="email" name="email" value="{{ $admin->email }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">手机</label>
                        <div class="layui-input-block">
                            <input type="text" name="phone" value="{{ $admin->phone }}" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">角色</label>
                        <div class="layui-input-block">
                            <input type="text" value="{{ $admin->roles->pluck('display_name')->join(', ') }}" readonly class="layui-input layui-disabled">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">注册时间</label>
                        <div class="layui-input-block">
                            <input type="text" value="{{ $admin->created_at ? $admin->created_at->format('Y-m-d H:i:s') : '' }}" readonly class="layui-input layui-disabled">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">最后登录</label>
                        <div class="layui-input-block">
                            <input type="text" value="{{ $admin->last_login_at ? $admin->last_login_at->format('Y-m-d H:i:s') : '从未登录' }}" readonly class="layui-input layui-disabled">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
                        保存
                    </button>
                    <button type="reset" class="pear-btn pear-btn-md">
                        重置
                    </button>
                </div>
            </div>

        </form>

        <style>
            .avatar-container {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .avatar-preview {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #f0f0f0;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }

            .avatar-preview:hover {
                border-color: #1890ff;
                box-shadow: 0 4px 12px rgba(24,144,255,0.3);
            }

            .avatar-container button {
                margin-left: 10px;
            }
        </style>

        <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
        <script src="/static/admin/component/pear/pear.js"></script>
        <script>

            // 相关接口
            const UPDATE_API = "{{ route('lpadmin.profile.update') }}";

            // 字段 头像 avatar
            layui.use(["upload", "layer"], function() {
                let input = layui.$("#avatar").prev();
                let avatarImg = layui.$(".avatar-preview");
                avatarImg.attr("src", input.val() || '/static/admin/images/avatar.png');

                layui.upload.render({
                    elem: "#avatar",
                    url: "{{ route('lpadmin.upload.avatar') }}",
                    acceptMime: "image/gif,image/jpeg,image/jpg,image/png",
                    field: "file",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    done: function (res) {
                        if (res.code !== 0) return layui.layer.msg(res.message);
                        this.item.prev().val(res.data.url);
                        layui.$(".avatar-preview").attr("src", res.data.url);
                    }
                });
            });

            //提交事件
            layui.use(["form", "popup"], function () {
                layui.form.on("submit(save)", function (data) {
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
                            return layui.popup.success("保存成功", function () {
                                // 刷新父页面的用户信息
                                if (parent && parent.location) {
                                    parent.location.reload();
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
