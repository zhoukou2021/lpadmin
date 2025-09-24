<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 页面未找到</title>
    <link rel="stylesheet" href="{{ asset('static/admin/component/pear/css/pear.css') }}" />
    <link rel="stylesheet" href="{{ asset('static/admin/css/pages/exception.css') }}" />
</head>
<body>
    <div class="pear-exception">
        <div class="pear-exception-container">
            <div class="icon">
                <div style="width: 250px; height: 295px; margin-bottom: 10px;">
                    <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M149.5 98.5c0 27.614-22.386 50-50 50s-50-22.386-50-50 22.386-50 50-50 50 22.386 50 50z" fill="#3742fa"></path><path d="M99.5 73.5c-13.807 0-25 11.193-25 25s11.193 25 25 25 25-11.193 25-25-11.193-25-25-25zm0 40c-8.284 0-15-6.716-15-15s6.716-15 15-15 15 6.716 15 15-6.716 15-15 15z" fill="#FFF"></path><path d="M99.5 88.5c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10z" fill="#3742fa"></path><path d="M180 120h40v8h-40zM180 140h40v8h-40zM180 160h40v8h-40z" fill="#3742fa"></path><path d="M30 120h40v8H30zM30 140h40v8H30zM30 160h40v8H30z" fill="#3742fa"></path><path d="M99.5 180c-27.614 0-50-22.386-50-50h8c0 23.196 18.804 42 42 42s42-18.804 42-42h8c0 27.614-22.386 50-50 50z" fill="#3742fa"></path><circle cx="75" cy="75" r="3" fill="#ff6b6b"></circle><circle cx="125" cy="75" r="3" fill="#ff6b6b"></circle><circle cx="200" cy="50" r="2" fill="#ffa502"></circle><circle cx="50" cy="200" r="2" fill="#ffa502"></circle><circle cx="220" cy="180" r="1.5" fill="#2ed573"></circle><circle cx="30" cy="50" r="1.5" fill="#2ed573"></circle></g></svg>
                </div>
            </div>
            <div class="title">
                <p class="error-code-404">404</p>
            </div>
            <div class="description">
                {{ $message ?? '抱歉，您访问的页面不存在或已被删除' }}
            </div>
            <div class="extra">
                <button class="layui-btn layui-btn-sm" onclick="goBack()">
                    <i class="layui-icon layui-icon-return"></i>
                    <span>返回上页</span>
                </button>
                <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="goHome()">
                    <i class="layui-icon layui-icon-home"></i>
                    <span>返回首页</span>
                </button>
                <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="searchPage()">
                    <i class="layui-icon layui-icon-search"></i>
                    <span>搜索内容</span>
                </button>
            </div>
        </div>
    </div>

    <script src="{{ asset('static/admin/component/layui/layui.js') }}"></script>
    <script src="{{ asset('static/admin/component/pear/pear.js') }}"></script>
    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                goHome();
            }
        }

        function goHome() {
            window.location.href = '{{ route("lpadmin.dashboard.index") }}';
        }

        function searchPage() {
            // 可以跳转到搜索页面或显示搜索框
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.prompt({
                    title: '搜索内容',
                    formType: 0,
                    btn: ['搜索', '取消']
                }, function(value, index){
                    if(value.trim()) {
                        // 这里可以实现搜索功能
                        layer.msg('搜索功能待实现: ' + value);
                    }
                    layer.close(index);
                });
            });
        }
    </script>
</body>
</html>
