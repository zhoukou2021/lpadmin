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
    <link rel="stylesheet" href="{{ asset('static/admin/css/pearadmin.css') }}">
    <link rel="stylesheet" href="{{ asset('static/admin/css/other/login.css') }}">
    
    @stack('styles')
</head>
<body class="pear-container">
    
    @yield('content')
    
    <!-- JavaScript -->
    <script src="{{ asset('static/admin/js/layui.js') }}"></script>
    <script src="{{ asset('static/admin/js/pearadmin.js') }}"></script>
    
    <script>
        // 全局配置
        window.LPADMIN_CONFIG = {
            base_url: '{{ url('/') }}',
            admin_url: '{{ route('lpadmin.dashboard.index') }}',
            csrf_token: '{{ csrf_token() }}',
            system: @json(config('lpadmin.system')),
        };
        
        // 设置CSRF Token
        layui.use(['jquery'], function(){
            var $ = layui.jquery;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
