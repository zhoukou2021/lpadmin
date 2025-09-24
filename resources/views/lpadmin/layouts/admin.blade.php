<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', '后台管理') - {{ config('lpadmin.system.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ config('lpadmin.system.favicon') }}" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('static/admin/css/layui.css') }}">
    <link rel="stylesheet" href="{{ asset('static/admin/css/pearadmin.css') }}">
    <link rel="stylesheet" href="{{ asset('static/admin/css/admin.css') }}">
    
    @stack('styles')
</head>
<body class="pear-container">
    
    <!-- 顶部导航 -->
    <div class="layui-header">
        <div class="layui-logo">
            <img src="{{ config('lpadmin.system.logo') }}" alt="Logo">
            <span>{{ config('lpadmin.system.name') }}</span>
        </div>
        
        <!-- 顶部导航菜单 -->
        <ul class="layui-nav layui-layout-left">
            <li class="layui-nav-item">
                <a href="{{ route('lpadmin.dashboard.index') }}">
                    <i class="layui-icon layui-icon-home"></i> 首页
                </a>
            </li>
        </ul>
        
        <!-- 顶部右侧菜单 -->
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item layui-hide layui-show-md-inline-block">
                <a href="javascript:;" class="fullscreen-btn">
                    <i class="layui-icon layui-icon-screen-full"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-header-event="menuRight" lay-unselect>
                <a href="javascript:;">
                    <img src="{{ $lpadmin_admin->avatar_url }}" class="layui-nav-img">
                    {{ $lpadmin_admin->nickname ?: $lpadmin_admin->username }}
                    <span class="layui-nav-more"></span>
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="javascript:;" id="userInfo">个人信息</a></dd>
                    <dd><a href="javascript:;" id="changePassword">修改密码</a></dd>
                    <dd><a href="javascript:;" id="logout">退出登录</a></dd>
                </dl>
            </li>
        </ul>
    </div>
    
    <!-- 左侧导航 -->
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <ul class="layui-nav layui-nav-tree" lay-filter="side-nav">
                @foreach($lpadmin_menu as $menu)
                    <li class="layui-nav-item @if(isset($menu['children']) && count($menu['children']) > 0) layui-nav-itemed @endif">
                        <a href="@if(empty($menu['children'])){{ $menu['url'] ?: 'javascript:;' }}@else javascript:; @endif">
                            <i class="layui-icon {{ $menu['icon'] ?: 'layui-icon-app' }}"></i>
                            <span>{{ $menu['title'] }}</span>
                        </a>
                        @if(isset($menu['children']) && count($menu['children']) > 0)
                            <dl class="layui-nav-child">
                                @foreach($menu['children'] as $child)
                                    <dd>
                                        <a href="{{ $child['url'] ?: 'javascript:;' }}">
                                            <i class="layui-icon {{ $child['icon'] ?: 'layui-icon-right' }}"></i>
                                            {{ $child['title'] }}
                                        </a>
                                    </dd>
                                @endforeach
                            </dl>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    
    <!-- 主体内容 -->
    <div class="layui-body">
        <!-- 面包屑导航 -->
        <div class="layui-breadcrumb" lay-filter="breadcrumb">
            <a href="{{ route('lpadmin.dashboard.index') }}">首页</a>
            @yield('breadcrumb')
        </div>
        
        <!-- 页面内容 -->
        <div class="layui-fluid">
            @yield('content')
        </div>
    </div>
    
    <!-- 底部信息 -->
    <div class="layui-footer">
        {{ config('lpadmin.system.copyright') }}
    </div>
    
    <!-- JavaScript -->
    <script src="{{ asset('static/admin/js/layui.js') }}"></script>
    <script src="{{ asset('static/admin/js/pearadmin.js') }}"></script>
    
    <script>
        // 全局配置
        window.LPADMIN_CONFIG = {
            base_url: '{{ url('/') }}',
            admin_url: '{{ route('lpadmin.dashboard.index') }}',
            csrf_token: '{{ csrf_token() }}',
            system: @json(config('lpadmin.system'))
        };

        layui.use(['element', 'layer', 'util'], function(){
            var element = layui.element;
            var layer = layui.layer;
            var util = layui.util;
            var $ = layui.$;
            
            // 设置CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // 全屏切换
            $('.fullscreen-btn').on('click', function(){
                var icon = $(this).find('i');
                if(icon.hasClass('layui-icon-screen-full')){
                    // 进入全屏
                    var docElm = document.documentElement;
                    if(docElm.requestFullscreen) {
                        docElm.requestFullscreen();
                    } else if(docElm.mozRequestFullScreen) {
                        docElm.mozRequestFullScreen();
                    } else if(docElm.webkitRequestFullScreen) {
                        docElm.webkitRequestFullScreen();
                    } else if(docElm.msRequestFullscreen) {
                        docElm.msRequestFullscreen();
                    }
                    icon.removeClass('layui-icon-screen-full').addClass('layui-icon-screen-restore');
                } else {
                    // 退出全屏
                    if(document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if(document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if(document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    } else if(document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                    icon.removeClass('layui-icon-screen-restore').addClass('layui-icon-screen-full');
                }
            });
            
            // 退出登录
            $('#logout').on('click', function(){
                layer.confirm('确定要退出登录吗？', {icon: 3, title:'提示'}, function(index){
                    $.post('{{ route('lpadmin.logout') }}', function(res){
                        if(res.code === 0){
                            layer.msg('退出成功', {icon: 1}, function(){
                                location.href = '{{ route('lpadmin.login') }}';
                            });
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    });
                    layer.close(index);
                });
            });
            
            // 个人信息
            $('#userInfo').on('click', function(){
                layer.open({
                    type: 2,
                    title: '个人信息',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['600px', '500px'],
                    content: '{{ route('lpadmin.profile') }}'
                });
            });
            
            // 修改密码
            $('#changePassword').on('click', function(){
                layer.open({
                    type: 2,
                    title: '修改密码',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['500px', '400px'],
                    content: '{{ route('lpadmin.change_password') }}'
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
