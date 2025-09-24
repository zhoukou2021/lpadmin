<!DOCTYPE html>
<html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <title>编辑管理员</title>
        <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
        <link rel="stylesheet" href="/static/admin/css/reset.css" />
    </head>
    <body>

        <form class="layui-form">

            <div class="mainBox">
                <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">角色</label>
                        <div class="layui-input-block">
                            <div name="roles" id="roles" value="" ></div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" data-value="1" title="启用" lay-filter="status">
                            <input type="radio" name="status" data-value="0" title="禁用" lay-filter="status">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="username" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label required">昵称</label>
                        <div class="layui-input-block">
                            <input type="text" name="nickname" value="" required lay-verify="required" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password" value="" class="layui-input" placeholder="不更新密码请留空" lay-verify="passwordCheck">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">确认密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password_confirmation" value="" class="layui-input" placeholder="不更新密码请留空" lay-verify="confirmPassword">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">头像</label>
                        <div class="layui-input-block">
                            <div class="avatar-selector">
                                <div class="avatar-preview-container" style="margin-bottom: 10px;">
                                    <img id="avatar-preview" src="/static/images/default-avatar.png"
                                         style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #e6e6e6; cursor: pointer;"
                                         onclick="selectAdminAvatar()" title="点击选择头像">
                                </div>
                                <div class="avatar-actions">
                                    <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" onclick="selectAdminAvatar()">
                                        <i class="layui-icon layui-icon-picture"></i> 选择头像
                                    </button>
                                    <button type="button" class="pear-btn pear-btn-warm pear-btn-sm" onclick="clearAdminAvatar()">
                                        <i class="layui-icon layui-icon-delete"></i> 清除
                                    </button>
                                </div>
                                <input type="hidden" name="avatar" id="avatar-input" value="">
                                <div class="layui-form-mid layui-word-aux">点击图片或按钮选择头像，支持jpg、png格式</div>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">邮箱</label>
                        <div class="layui-input-block">
                            <input type="email" name="email" value="" class="layui-input">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">手机</label>
                        <div class="layui-input-block">
                            <input type="text" name="phone" value="" class="layui-input">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bottom">
                <div class="button-container">
                    <button type="submit" class="pear-btn pear-btn-primary pear-btn-md" lay-submit="" lay-filter="save">
                        提交
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
        <script src="/static/admin/js/radio-fix.js"></script>
        <script>

            // 相关接口
            const PRIMARY_KEY = "id";
            const SELECT_API = "{{ route('lpadmin.admin.show', $admin->id ?? ':id') }}";
            const UPDATE_API = "{{ route('lpadmin.admin.update', $admin->id ?? ':id') }}";

            // 全局头像管理函数
            function selectAdminAvatar() {
                layui.layer.open({
                    type: 2,
                    title: '选择管理员头像',
                    area: ['80%', '70%'],
                    content: '/lpadmin/upload/selector?type=image&mode=single&callback=setAdminAvatar'
                });
            }

            function setAdminAvatar(selectedFiles) {
                if (selectedFiles.length > 0) {
                    let file = selectedFiles[0];
                    layui.$('#avatar-preview').attr('src', file.url);
                    layui.$('#avatar-input').val(file.url);
                    layui.layer.msg('头像设置成功', {icon: 1});
                }
            }

            function clearAdminAvatar() {
                layui.$('#avatar-preview').attr('src', '/static/images/default-avatar.png');
                layui.$('#avatar-input').val('');
                layui.layer.msg('头像已清除', {icon: 1});
            }

            // 获取数据库记录
            layui.use(["form", "util", "popup"], function () {
                let $ = layui.$;

                // 初始化单选框
                if (window.RadioHelper) {
                    RadioHelper.init('status');
                }

                // 从URL获取ID
                let urlParams = new URLSearchParams(window.location.search);
                let adminId = urlParams.get('id') || '{{ $admin->id ?? "" }}';

                if (adminId) {
                    let apiUrl = SELECT_API.replace(':id', adminId);

                    $.ajax({
                        url: apiUrl,
                        dataType: "json",
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            let adminData = res.data || res;

                            // 给表单初始化数据
                            layui.each(adminData, function (key, value) {
                                let obj = $('*[name="'+key+'"]');
                                if (key === "password") {
                                    obj.attr("placeholder", "不更新密码请留空");
                                    return;
                                }
                                if (typeof obj[0] === "undefined" || !obj[0].nodeName) return;
                                if (obj[0].nodeName.toLowerCase() === "textarea") {
                                    obj.val(layui.util.escape(value));
                                } else {
                                    obj.attr("value", value);
                                }
                            });

                            // 初始化头像显示
                            let currentAvatar = adminData.avatar || '/static/images/default-avatar.png';
                            layui.$('#avatar-preview').attr('src', currentAvatar);
                            layui.$('#avatar-input').val(adminData.avatar || '');

                            // 设置状态单选框（使用简化的RadioHelper）
                            RadioHelper.setValue('status', adminData.status);

                            // 重新渲染表单
                            layui.form.render();



                            // 字段 角色 roles
                            layui.use(["jquery", "xmSelect"], function() {
                                layui.$.ajax({
                                    url: "{{ route('lpadmin.role.index') }}?format=tree",
                                    dataType: "json",
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    success: function (res) {
                                        let roleIds = [];
                                        if (adminData.roles && adminData.roles.length > 0) {
                                            roleIds = adminData.roles.map(role => role.id.toString());
                                        }

                                        layui.xmSelect.render({
                                            el: "#roles",
                                            name: "roles",
                                            initValue: roleIds,
                                            data: res.data || [],
                                            layVerify: "required",
                                            tree: {show: true, expandedKeys: true, strict: false},
                                            toolbar: {show: true, list: ["ALL","CLEAR","REVERSE"]},
                                        })

                                        if (res.code && res.code !== 0) {
                                            layui.popup.failure(res.message);
                                        }
                                    }
                                });
                            });

                            // ajax产生错误
                            if (res.code && res.code !== 0) {
                                layui.popup.failure(res.message);
                            }

                        }
                    });
                }
            });

            //提交事件
            layui.use(["form", "popup"], function () {
                // 自定义验证规则
                layui.form.verify({
                    passwordCheck: function(value, item) {
                        if (value && value.length < 6) {
                            return '密码至少6位';
                        }
                    },
                    confirmPassword: function(value, item) {
                        var password = layui.$('input[name="password"]').val();
                        // 如果密码字段有值，确认密码也必须有值且一致
                        if (password && !value) {
                            return '请确认密码';
                        }
                        if (password && value && value !== password) {
                            return '两次密码输入不一致';
                        }
                        // 如果密码字段为空，确认密码也应该为空
                        if (!password && value) {
                            return '请先输入密码';
                        }
                    }
                });

                // 监听状态变化（使用简化的RadioHelper）
                RadioHelper.onChange('status', function(value, element, data) {
                    console.log('状态改变为:', value);
                });

                layui.form.on("submit(save)", function (data) {
                    let urlParams = new URLSearchParams(window.location.search);
                    let adminId = urlParams.get('id') || '{{ $admin->id ?? "" }}';

                    data.field._token = '{{ csrf_token() }}';
                    data.field._method = 'PUT';

                    // 修复单选框数据
                    RadioHelper.fixFormData(data.field, ['status']);

                    console.log('提交的表单数据:', data.field);

                    let apiUrl = UPDATE_API.replace(':id', adminId);

                    layui.$.ajax({
                        url: apiUrl,
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
