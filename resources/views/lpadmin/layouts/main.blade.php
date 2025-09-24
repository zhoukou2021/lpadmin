<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', config('lpadmin.system.name', 'LPadmin'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- 依赖样式 -->
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <!-- 加载样式 -->
    <link rel="stylesheet" href="/static/admin/css/loader.css" />
    <!-- 布局样式 -->
    <link rel="stylesheet" href="/static/admin/css/admin.css" />
    <!-- 重置样式 -->
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    @stack('styles')
</head>
<!-- 结构代码 -->
<body class="layui-layout-body pear-admin">
    <!-- 布局框架 -->
    <div class="layui-layout layui-layout-admin">
        <!-- 顶部样式 -->
        <div class="layui-header">
            <!-- 菜单顶部 -->
            <div class="layui-logo">
                <!-- 图标 -->
                <img class="logo" src="/static/admin/images/logo.png">
                <!-- 标题 -->
                <span class="title">{{ config('lpadmin.system.name', 'LPadmin') }}</span>
            </div>
            <!-- 顶部左侧功能 -->
            <ul class="layui-nav layui-layout-left">
                <li class="collapse layui-nav-item"><a href="#" class="layui-icon layui-icon-shrink-right"></a></li>
                <li class="refresh layui-nav-item"><a href="#" class="layui-icon layui-icon-refresh-1" loading="600"></a></li>
            </ul>
            <!-- 多系统菜单 -->
            <div id="control" class="layui-layout-control"></div>
            <!-- 顶部右侧菜单 -->
            <ul class="layui-nav layui-layout-right">
                <li class="layui-nav-item layui-hide-xs"><a href="#" class="menuSearch layui-icon layui-icon-search"></a></li>
                <li class="layui-nav-item layui-hide-xs"><a href="#" class="fullScreen layui-icon layui-icon-screen-full"></a></li>
                <li class="layui-nav-item layui-hide-xs message"></li>
                <li class="layui-nav-item user">
                    <!-- 头像 -->
                    <a href="javascript:;">
                        <img src="{{ auth('lpadmin')->user()->avatar_url ?? '/static/admin/images/avatar.png' }}" class="layui-nav-img">
                        {{ auth('lpadmin')->user()->nickname ?? auth('lpadmin')->user()->username ?? 'Admin' }}
                    </a>
                    <!-- 功能菜单 -->
                    <dl class="layui-nav-child">
                        <dd><a user-menu-url="{{ route('lpadmin.profile') }}" user-menu-id="10" user-menu-title="基本资料">基本资料</a></dd>
                        <dd><a user-menu-url="{{ route('lpadmin.change_password') }}" user-menu-id="11" user-menu-title="修改密码">修改密码</a></dd>
                        <dd><a href="javascript:void(0);" class="logout">注销登录</a></dd>
                    </dl>
                </li>
                <!-- 主题配置 -->
                <li class="layui-nav-item setting"><a href="#" class="layui-icon layui-icon-more-vertical"></a></li>
            </ul>
        </div>
        <!-- 侧边区域 -->
        <div class="layui-side layui-bg-black">
            <!-- 菜单顶部 -->
            <div class="layui-logo">
                <!-- 图标 -->
                <img class="logo" src="/static/admin/images/logo.png">
                <!-- 标题 -->
                <a href="{{ route('lpadmin.dashboard.index') }}"><span class="title">{{ config('lpadmin.system.name', 'LPadmin') }}</span></a>
            </div>
            <!-- 菜单内容 -->
            <div class="layui-side-scroll">
                <div id="sideMenu"></div>
            </div>
        </div>
        <!-- 视图页面 -->
        <div class="layui-body">
            <!-- 内容页面 -->
            <div id="content">
                @yield('content')
            </div>
        </div>
        <!-- 页脚 -->
        <div class="layui-footer layui-text">
            <span class="left">
                {{ config('lpadmin.system.copyright', 'Released under the MIT license.') }}
            </span>
            <span class="center"></span>
        </div>
        <!-- 遮盖层 -->
        <div class="pear-cover"></div>
        <!-- 加载动画 -->
        <div class="loader-main">
            <!-- 动画对象 -->
            <div class="loader"></div>
        </div>
    </div>
    <!-- 移动端便捷操作 -->
    <div class="pear-collapsed-pe collapse">
        <a href="#" class="layui-icon layui-icon-shrink-right"></a>
    </div>
    <!-- 依赖脚本 -->
    <script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
    <script src="/static/admin/component/pear/pear.js"></script>
    <!-- 框架初始化 -->
    <script>
        // Admin
        window.Admin = {
            Account: {}
        };

        layui.use(["admin","jquery","popup","drawer"], function() {
            var $ = layui.$;
            var admin = layui.admin;
            var popup = layui.popup;

            admin.setConfigType("json");
            admin.setConfigPath("/static/admin/config/pear.config.json");

            admin.render();

            // 登出逻辑
            admin.logout(function(){
                $.ajax({
                    url: "{{ route('lpadmin.logout') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code) {
                            return popup.error(res.message || res.msg);
                        }
                        popup.success("注销成功",function(){
                            location.href = "{{ route('lpadmin.login') }}";
                        })
                    }
                });
                return false;
            })

            // 获取用户信息
            $.ajax({
                url: "{{ route('lpadmin.profile') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (res) {
                    if (!res.code) {
                        window.Admin.Account = res.data;
                    }
                }
            });

            // 消息点击回调
            //admin.message(function(id, title, context, form) {});
        });
    </script>

    @stack('scripts')
</body>
</html>
