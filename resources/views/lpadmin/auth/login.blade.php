<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>登录 - {{ config('lpadmin.system.name', 'LPadmin') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- 样式文件 -->
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/pages/login.css" />
</head>
<!-- 代码结构 -->
<body background="/static/admin/images/background.svg" style="background-size: cover;">
    <div class="login-page">
        <div class="layui-row">
            <!-- 左侧插画区域 -->
            <div class="layui-col-md6 login-bg">
                <img src="/static/admin/images/login-illustration.svg" alt="登录插画" class="login-bg-img">
            </div>

            <!-- 右侧登录表单区域 -->
            <div class="layui-col-md6 login-form">
                <div class="form-center">
                    <div class="form-center-box">
                        <!-- 标题区域 -->
                        <div class="top-log-title">
                            <img class="top-log" src="/static/admin/images/logo.png" alt="Logo" />
                            <span>{{ config('lpadmin.system.name', 'LPadmin') }}</span>
                        </div>
                        <div class="top-desc">以超平凡的速度构建内部工具</div>

                        <!-- 登录表单 -->
                        <form class="layui-form login-form-box" id="loginForm">
                            @csrf
                            <div class="layui-form-item">
                                <div class="layui-input-wrap">
                                    <div class="layui-input-prefix">
                                        <i class="layui-icon layui-icon-username"></i>
                                    </div>
                                    <input lay-verify="required" class="layui-input" type="text" name="username" value="{{ old('username') }}" placeholder="账户" />
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-input-wrap">
                                    <div class="layui-input-prefix">
                                        <i class="layui-icon layui-icon-password"></i>
                                    </div>
                                    <input lay-verify="required|minLength" minlength="6" class="layui-input" type="password" name="password" value="" placeholder="密码" />
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-input-wrap captcha-wrap">
                                    <div class="layui-input-prefix">
                                        <i class="layui-icon layui-icon-vercode"></i>
                                    </div>
                                    <input lay-verify="required" class="layui-input captcha-input" name="captcha" placeholder="验证码" />
                                    <div class="captcha-image-wrap">
                                        <img class="codeImage" onclick="switchCaptcha()" style="cursor: pointer;" />
                                    </div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <input type="checkbox" name="remember" lay-skin="primary" title="自动登录">
                            </div>

                            <div class="layui-form-item">
                                <button type="submit" class="layui-btn layui-btn-fluid login-btn" lay-submit lay-filter="login">
                                    登录
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            @foreach ($errors->all() as $error)
                <div class="layui-alert layui-alert-danger">{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <script>
        var color = localStorage.getItem("theme-color-color");
        var second = localStorage.getItem("theme-color-second");
        if (!color || !second) {
            localStorage.setItem("theme-color-color", "#2d8cf0");
            localStorage.setItem("theme-color-second", "#ecf5ff");
        }
    </script>
    <!-- 资源引入 -->
    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <script>
        layui.use(['form', 'button', 'popup', 'layer', 'theme', 'admin'], function() {
            var $ = layui.$, layer = layui.layer, form = layui.form;

            // 自定义验证规则
            form.verify({
                minLength: function(value, item) {
                    var minLength = parseInt(item.getAttribute('minlength')) || 6;
                    if (value.length < minLength) {
                        return '密码至少' + minLength + '位';
                    }
                }
            });

            function switchCaptcha() {
                $('.codeImage').attr("src", "{{ route('lpadmin.captcha') }}?v=" + new Date().getTime());
            }
            switchCaptcha();

            // 登录提交
            form.on('submit(login)', function (data) {
                layer.load();
                $.ajax({
                    url: '{{ route('lpadmin.login') }}',
                    type: "POST",
                    data: data.field,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        layer.closeAll('loading');
                        if (!res.code) {
                            layui.popup.success('登录成功', function () {
                                location.href = res.data.redirect || '{{ route('lpadmin.index') }}';
                            })
                        } else {
                            layui.popup.failure(res.message || res.msg);
                            switchCaptcha();
                        }
                    },
                    error: function(xhr) {
                        layer.closeAll('loading');
                        var res = xhr.responseJSON;
                        layui.popup.failure(res ? (res.message || res.msg) : '登录失败');
                        switchCaptcha();
                    }
                });
                return false;
            });

            // 验证码切换函数
            function switchCaptcha() {
                $('.codeImage').attr("src", "{{ route('lpadmin.captcha') }}?v=" + new Date().getTime());
            }

            // 初始化验证码
            switchCaptcha();

            // 验证码点击事件
            $('.codeImage').on('click', function () {
                switchCaptcha();
            });

            // 密码显示切换
            $('.password-toggle').on('click', function() {
                var passwordInput = $(this).closest('.layui-input-wrap').find('input[name="password"]');
                var icon = $(this);

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('layui-icon-eye').addClass('layui-icon-eye-invisible');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('layui-icon-eye-invisible').addClass('layui-icon-eye');
                }
            });

            // 全局函数，供onclick调用
            window.switchCaptcha = switchCaptcha;
        })
    </script>
</body>
</html>
