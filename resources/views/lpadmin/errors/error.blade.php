<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code ?? '错误' }} - {{ $title ?? '系统错误' }}</title>
    <link rel="stylesheet" href="{{ asset('static/admin/component/pear/css/pear.css') }}" />
    <link rel="stylesheet" href="{{ asset('static/admin/css/pages/exception.css') }}" />
</head>
<body>
    <div class="pear-exception">
        <div class="pear-exception-container">
            <div class="icon">
                <div style="width: 250px; height: 295px; margin-bottom: 10px;">
                    @if(isset($code))
                        @if($code == 403)
                            {{-- 403 权限错误图标 --}}
                            <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 60c35.898 0 65 29.102 65 65s-29.102 65-65 65-65-29.102-65-65 29.102-65 65-65z" fill="#ff6b6b"></path><path d="M125 85c19.33 0 35 15.67 35 35s-15.67 35-35 35-35-15.67-35-35 15.67-35 35-35z" fill="#FFF"></path><path d="M110 110h30v20h-30z" fill="#ff6b6b"></path><path d="M115 115h20v10h-20z" fill="#FFF"></path></g></svg>
                        @elseif($code == 404)
                            {{-- 404 页面未找到图标 --}}
                            <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M149.5 98.5c0 27.614-22.386 50-50 50s-50-22.386-50-50 22.386-50 50-50 50 22.386 50 50z" fill="#3742fa"></path><path d="M99.5 73.5c-13.807 0-25 11.193-25 25s11.193 25 25 25 25-11.193 25-25-11.193-25-25-25zm0 40c-8.284 0-15-6.716-15-15s6.716-15 15-15 15 6.716 15 15-6.716 15-15 15z" fill="#FFF"></path><circle cx="75" cy="75" r="3" fill="#ff6b6b"></circle><circle cx="125" cy="75" r="3" fill="#ff6b6b"></circle></g></svg>
                        @elseif($code == 500)
                            {{-- 500 服务器错误图标 --}}
                            <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 50c41.421 0 75 33.579 75 75s-33.579 75-75 75-75-33.579-75-75 33.579-75 75-75z" fill="#ff3838"></path><path d="M125 75c27.614 0 50 22.386 50 50s-22.386 50-50 50-50-22.386-50-50 22.386-50 50-50z" fill="#FFF"></path><path d="M110 110h30v5h-30zM110 120h30v5h-30zM110 130h30v5h-30z" fill="#ff3838"></path></g></svg>
                        @else
                            {{-- 通用错误图标 --}}
                            <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 60c35.898 0 65 29.102 65 65s-29.102 65-65 65-65-29.102-65-65 29.102-65 65-65z" fill="#ffa502"></path><path d="M125 85c19.33 0 35 15.67 35 35s-15.67 35-35 35-35-15.67-35-35 15.67-35 35-35z" fill="#FFF"></path><path d="M115 110h20v5h-20zM120 120h10v10h-10z" fill="#ffa502"></path></g></svg>
                        @endif
                    @else
                        {{-- 默认错误图标 --}}
                        <svg width="251" height="294" style="width: auto; height: auto;"><g fill="none" fillRule="evenodd"><path d="M0 129.023v-2.084C0 58.364 55.591 2.774 124.165 2.774h2.085c68.574 0 124.165 55.59 124.165 124.165v2.084c0 68.575-55.59 124.166-124.165 124.166h-2.085C55.591 253.189 0 197.598 0 129.023" fill="#E4EBF7"></path><path d="M125 60c35.898 0 65 29.102 65 65s-29.102 65-65 65-65-29.102-65-65 29.102-65 65-65z" fill="#ffa502"></path><path d="M125 85c19.33 0 35 15.67 35 35s-15.67 35-35 35-35-15.67-35-35 15.67-35 35-35z" fill="#FFF"></path><path d="M115 110h20v5h-20zM120 120h10v10h-10z" fill="#ffa502"></path></g></svg>
                    @endif
                </div>
            </div>
            <div class="title">
                <p class="error-code-{{ $code ?? 'default' }}">{{ $code ?? '错误' }}</p>
            </div>
            <div class="description">
                {{ $message ?? '系统遇到了一个错误，请稍后再试' }}
            </div>
            <div class="extra">
                @if(isset($code) && $code == 403)
                    <button class="layui-btn layui-btn-sm" onclick="goBack()">
                        <i class="layui-icon layui-icon-return"></i>
                        <span>返回上页</span>
                    </button>
                    <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="goHome()">
                        <i class="layui-icon layui-icon-home"></i>
                        <span>返回首页</span>
                    </button>
                    <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="contactAdmin()">
                        <i class="layui-icon layui-icon-service"></i>
                        <span>联系管理员</span>
                    </button>
                @elseif(isset($code) && $code == 404)
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
                @elseif(isset($code) && $code == 500)
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
                @else
                    <button class="layui-btn layui-btn-sm" onclick="goBack()">
                        <i class="layui-icon layui-icon-return"></i>
                        <span>返回上页</span>
                    </button>
                    <button class="layui-btn layui-btn-sm layui-btn-primary" onclick="goHome()">
                        <i class="layui-icon layui-icon-home"></i>
                        <span>返回首页</span>
                    </button>
                @endif
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

        function refreshPage() {
            window.location.reload();
        }

        function searchPage() {
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.prompt({
                    title: '搜索内容',
                    formType: 0,
                    btn: ['搜索', '取消']
                }, function(value, index){
                    if(value.trim()) {
                        layer.msg('搜索功能待实现: ' + value);
                    }
                    layer.close(index);
                });
            });
        }

        function contactAdmin() {
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.open({
                    type: 1,
                    title: '联系管理员',
                    area: ['500px', '350px'],
                    content: `
                        <div style="padding: 20px;">
                            <div style="margin-bottom: 15px;">
                                <p style="color: #666; line-height: 1.6;">
                                    如果您认为这是一个错误，或者需要访问此页面的权限，请联系系统管理员。
                                </p>
                            </div>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                                <p style="margin: 0; color: #333;"><strong>管理员邮箱：</strong> {{ config('lpadmin.system.contact.email', 'admin@example.com') }}</p>
                                <p style="margin: 5px 0 0 0; color: #333;"><strong>联系电话：</strong> {{ config('lpadmin.system.contact.phone', '暂未设置') }}</p>
                            </div>
                            <div style="text-align: center;">
                                <button type="button" class="layui-btn layui-btn-primary" onclick="layer.closeAll()">关闭</button>
                            </div>
                        </div>
                    `
                });
            });
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
