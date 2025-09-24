<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - 服务器内部错误</title>
    <link rel="stylesheet" href="{{ asset('static/admin/component/pear/css/pear.css') }}" />
    <link rel="stylesheet" href="{{ asset('static/admin/css/pages/exception.css') }}" />
</head>
<body>
    <div class="pear-exception">
        <div class="pear-exception-container">
            <div class="icon">
                <div style="width: 250px; height: 295px; margin-bottom: 10px;">
                    <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 50c41.421 0 75 33.579 75 75s-33.579 75-75 75-75-33.579-75-75 33.579-75 75-75z" fill="#ff3838"></path><path d="M125 75c27.614 0 50 22.386 50 50s-22.386 50-50 50-50-22.386-50-50 22.386-50 50-50z" fill="#FFF"></path><path d="M125 100c13.807 0 25 11.193 25 25s-11.193 25-25 25-25-11.193-25-25 11.193-25 25-25z" fill="#ff3838"></path><path d="M110 110h30v5h-30zM110 120h30v5h-30zM110 130h30v5h-30z" fill="#FFF"></path><path d="M95 95l10 10M145 95l-10 10M95 155l10-10M145 155l-10-10" stroke="#ff6348" strokeWidth="3" strokeLinecap="round"></path><circle cx="80" cy="80" r="3" fill="#ff6b6b"></circle><circle cx="170" cy="80" r="3" fill="#ff6b6b"></circle><circle cx="80" cy="170" r="3" fill="#ff6b6b"></circle><circle cx="170" cy="170" r="3" fill="#ff6b6b"></circle><path d="M50 125h20v5H50zM180 125h20v5h-20z" fill="#ff3838"></path><circle cx="30" cy="30" r="2" fill="#ffa502"></circle><circle cx="220" cy="30" r="2" fill="#ffa502"></circle><circle cx="30" cy="220" r="2" fill="#ffa502"></circle><circle cx="220" cy="220" r="2" fill="#ffa502"></circle></g></svg>
                </div>
            </div>
            <div class="title">
                <p class="error-code-500">500</p>
            </div>
            <div class="description">
                {{ $message ?? '抱歉，服务器遇到了一个错误，请稍后再试' }}
            </div>
            <div class="extra">
                <button class="layui-btn layui-btn-sm" onclick="refreshPage()">
                    <i class="layui-icon layui-icon-refresh"></i>
                    <span>刷新页面</span>
                </button>
                <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="goHome()">
                    <i class="layui-icon layui-icon-home"></i>
                    <span>返回首页</span>
                </button>
                <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="reportError()">
                    <i class="layui-icon layui-icon-notice"></i>
                    <span>报告问题</span>
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

        function goHome() {
            window.location.href = '{{ route("lpadmin.dashboard.index") }}';
        }

        function reportError() {
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.open({
                    type: 1,
                    title: '报告问题',
                    area: ['500px', '400px'],
                    content: `
                        <div style="padding: 20px;">
                            <form class="layui-form">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">问题描述</label>
                                    <div class="layui-input-block">
                                        <textarea name="description" placeholder="请描述您遇到的问题" class="layui-textarea" rows="5"></textarea>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">联系方式</label>
                                    <div class="layui-input-block">
                                        <input type="text" name="contact" placeholder="邮箱或电话（可选）" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-input-block">
                                        <button type="button" class="layui-btn" onclick="submitReport()">提交报告</button>
                                        <button type="button" class="layui-btn layui-btn-primary" onclick="layer.closeAll()">取消</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    `
                });
            });
        }

        function submitReport() {
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.msg('感谢您的反馈，我们会尽快处理！', {icon: 1});
                layer.closeAll();
            });
        }
    </script>
</body>
</html>
