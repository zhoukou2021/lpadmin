<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - 页面已过期</title>
    <link rel="stylesheet" href="{{ asset('static/admin/component/pear/css/pear.css') }}" />
    <link rel="stylesheet" href="{{ asset('static/admin/css/pages/exception.css') }}" />
</head>
<body>
    <div class="pear-exception">
        <div class="pear-exception-container">
            <div class="icon">
                <div style="width: 250px; height: 295px; margin-bottom: 10px;">
                    <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 60c35.898 0 65 29.102 65 65s-29.102 65-65 65-65-29.102-65-65 29.102-65 65-65z" fill="#ffa502"></path><path d="M125 85c19.33 0 35 15.67 35 35s-15.67 35-35 35-35-15.67-35-35 15.67-35 35-35z" fill="#FFF"></path><path d="M115 110h20v5h-20z" fill="#ffa502"></path><path d="M120 120h10v10h-10z" fill="#ffa502"></path><circle cx="110" cy="95" r="2" fill="#ffa502"></circle><circle cx="140" cy="95" r="2" fill="#ffa502"></circle></g></svg>
                </div>
            </div>
            <div class="title">
                <p class="error-code-419">419</p>
            </div>
            <div class="description">
                {{ $message ?? '页面已过期，请刷新页面后重试' }}
            </div>
            <div class="extra">
                <button class="layui-btn layui-btn-sm" onclick="refreshPage()">
                    <i class="layui-icon layui-icon-refresh"></i>
                    <span>刷新页面</span>
                </button>
                <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="goBack()">
                    <i class="layui-icon layui-icon-return"></i>
                    <span>返回上页</span>
                </button>
                <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="goHome()">
                    <i class="layui-icon layui-icon-home"></i>
                    <span>返回首页</span>
                </button>
            </div>
        </div>
    </div>

    <script src="{{ asset('static/admin/component/layui/layui.js') }}"></script>
    <script src="{{ asset('static/admin/component/pear/pear.js') }}"></script>
    <script>
        function refreshPage() {
            window.location.reload();
        }

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
    </script>
</body>
</html>
