<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - 请求过于频繁</title>
    <link rel="stylesheet" href="{{ asset('static/admin/component/pear/css/pear.css') }}" />
    <link rel="stylesheet" href="{{ asset('static/admin/css/pages/exception.css') }}" />
</head>
<body>
    <div class="pear-exception">
        <div class="pear-exception-container">
            <div class="icon">
                <div style="width: 250px; height: 295px; margin-bottom: 10px;">
                    <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 60c35.898 0 65 29.102 65 65s-29.102 65-65 65-65-29.102-65-65 29.102-65 65-65z" fill="#ff9f43"></path><path d="M125 85c19.33 0 35 15.67 35 35s-15.67 35-35 35-35-15.67-35-35 15.67-35 35-35z" fill="#FFF"></path><path d="M115 110h20v5h-20zM115 120h20v5h-20z" fill="#ff9f43"></path><circle cx="105" cy="95" r="2" fill="#ff9f43"></circle><circle cx="125" cy="95" r="2" fill="#ff9f43"></circle><circle cx="145" cy="95" r="2" fill="#ff9f43"></circle></g></svg>
                </div>
            </div>
            <div class="title">
                <p class="error-code-429">429</p>
            </div>
            <div class="description">
                {{ $message ?? '请求过于频繁，请稍后再试' }}
            </div>
            <div class="extra">
                <button class="layui-btn layui-btn-sm" onclick="waitAndRetry()">
                    <i class="layui-icon layui-icon-time"></i>
                    <span id="retry-btn-text">等待重试 (30s)</span>
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
        let countdown = 30;
        let countdownTimer = null;

        function startCountdown() {
            const retryBtn = document.querySelector('#retry-btn-text');
            countdownTimer = setInterval(function() {
                countdown--;
                retryBtn.textContent = `等待重试 (${countdown}s)`;
                
                if (countdown <= 0) {
                    clearInterval(countdownTimer);
                    retryBtn.textContent = '重试';
                    document.querySelector('button[onclick="waitAndRetry()"]').onclick = function() {
                        window.location.reload();
                    };
                }
            }, 1000);
        }

        function waitAndRetry() {
            if (countdown > 0) {
                layui.use('layer', function(){
                    var layer = layui.layer;
                    layer.msg(`请等待 ${countdown} 秒后再试`, {icon: 2});
                });
            } else {
                window.location.reload();
            }
        }

        function goHome() {
            window.location.href = '{{ route("lpadmin.dashboard.index") }}';
        }

        // 页面加载时开始倒计时
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
    </script>
</body>
</html>
