<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>编辑用户</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/form-common.css" />
</head>
<body>

    <form class="layui-form" lay-filter="user-form">

        <div class="mainBox">
            <div class="main-container mr-5">

                    <div class="layui-form-item">
                        <label class="layui-form-label required">用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="username" value="{{ $user->username }}" class="layui-input" placeholder="请输入用户名" lay-verify="required">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password" value="" class="layui-input" placeholder="不修改密码请留空">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">确认密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password_confirmation" value="" class="layui-input" placeholder="请再次输入密码">
                        </div>
                    </div>
                    {{-- 使用通用单选框组件 --}}
                    @include('lpadmin.components.radio-group', [
                        'type' => 'gender',
                        'name' => 'gender',
                        'label' => '性别',
                        'required' => false,
                        'default' => $user->gender
                    ])
                    <div class="layui-form-item">
                        <label class="layui-form-label">昵称</label>
                        <div class="layui-input-block">
                            <input type="text" name="nickname" value="{{ $user->nickname }}" class="layui-input" placeholder="请输入昵称">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">邮箱</label>
                        <div class="layui-input-block">
                            <input type="email" name="email" value="{{ $user->email }}" class="layui-input" placeholder="请输入邮箱">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">手机号</label>
                        <div class="layui-input-block">
                            <input type="text" name="phone" value="{{ $user->phone }}" class="layui-input" placeholder="请输入手机号">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">头像</label>
                        <div class="layui-input-block">
                            @if(!empty($user->avatar))
                                <div style="margin-bottom:8px"><img src="{{ $user->avatar }}" style="height:40px"></div>
                            @endif
                            <input type="file" name="avatar_file" accept="image/*" class="layui-input">
                        </div>
                    </div>

                    

                    <div class="layui-form-item">
                        <label class="layui-form-label">备注</label>
                        <div class="layui-input-block">
                            <textarea name="remark" class="layui-textarea" placeholder="请输入备注">{{ $user->remark }}</textarea>
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

        </div>

    </form>

    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script src="/static/admin/js/radio-fix.js"></script>
    <script>

        // 相关接口
        const UPDATE_API = "{{ route('lpadmin.user.update', $user->id) }}";

        layui.use(["form", "popup", "layer"], function () {
            let form = layui.form;
            let layer = layui.layer;
            let $ = layui.$;

            // 初始化单选框
            if (window.RadioHelper) {
                RadioHelper.init('status');
                // 设置当前值
                RadioHelper.setValue('status', '{{ $user->status }}');
            }

            // 提交事件
            form.on("submit(save)", function (data) {
                // 修复单选框数据
                if (window.RadioHelper) {
                    RadioHelper.fixFormData(data.field, ['status']);
                }

                // 创建 FormData 对象以支持文件上传
                let formData = new FormData();
                
                // 添加所有表单字段
                for (let key in data.field) {
                    formData.append(key, data.field[key]);
                }

                // 添加文件（如果存在）
                let avatarFile = $('input[name="avatar_file"]')[0].files[0];
                if (avatarFile) {
                    formData.append('avatar_file', avatarFile);
                }

                // Laravel 需要 PUT 方法，但 FormData 不支持，使用 _method 字段模拟
                formData.append('_method', 'PUT');

                $.ajax({
                    url: UPDATE_API,
                    type: 'POST',
                    data: formData,
                    processData: false,  // 不处理数据，让 jQuery 自动处理 FormData
                    contentType: false,  // 不设置内容类型，让浏览器自动设置（包含 boundary）
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.code === 0) {
                            layui.popup.success(res.message, function () {
                                // 刷新父页面表格
                                if (parent.refreshTable && typeof parent.refreshTable === 'function') {
                                    parent.refreshTable();
                                }
                                // 关闭弹窗
                                if (parent.layer) {
                                    parent.layer.closeAll();
                                }
                            });
                        } else {
                            layui.popup.failure(res.message || '操作失败');
                        }
                    },
                    error: function (xhr, status, error) {
                        let errorMessage = '网络错误，请稍后重试';

                        // 尝试解析后台返回的错误信息
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // 如果不是JSON格式，检查是否是验证错误
                                if (xhr.status === 422) {
                                    errorMessage = '表单验证失败，请检查输入内容';
                                } else if (xhr.status === 419) {
                                    errorMessage = 'CSRF令牌失效，请刷新页面重试';
                                } else if (xhr.status === 500) {
                                    errorMessage = '服务器内部错误，请联系管理员';
                                }
                            }
                        }

                        layui.popup.failure(errorMessage);
                    }
                });
                return false;
            });

        });

    </script>

</body>
</html>
