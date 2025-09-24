@extends('home.layout')

@section('title', 'LPadmin 首页演示')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-home me-2"></i>LPadmin 首页演示</h3>
                </div>
                <div class="card-body p-5">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>演示说明：</strong>这是LPadmin系统的前端首页演示页面。
                    </div>
                    
                    <h4>🎨 设计特色</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>现代化渐变背景设计</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>响应式布局，支持多设备</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>流畅的滚动动画效果</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>优雅的配色方案</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>完整的SEO优化</li>
                    </ul>
                    
                    <h4>📋 功能模块</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-star text-warning me-2"></i>Hero展示区域</li>
                                <li><i class="fas fa-image text-info me-2"></i>后台截图展示</li>
                                <li><i class="fas fa-cogs text-secondary me-2"></i>核心功能介绍</li>
                                <li><i class="fas fa-code text-primary me-2"></i>技术栈展示</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-shield-alt text-success me-2"></i>系统优势展示</li>
                                <li><i class="fas fa-info-circle text-success me-2"></i>系统信息面板</li>
                                <li><i class="fas fa-envelope text-danger me-2"></i>联系方式展示</li>
                                <li><i class="fas fa-copyright text-muted me-2"></i>版权信息</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h4>🚀 快速访问</h4>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="/" class="btn btn-primary btn-lg me-md-2">
                            <i class="fas fa-home me-2"></i>查看首页
                        </a>
                        <a href="{{ lpadmin_url_prefix() }}/login" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>管理后台
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h4>📖 技术说明</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>前端技术</h6>
                            <ul class="small text-muted">
                                <li>Bootstrap 5.3.0</li>
                                <li>Font Awesome 6.4.0</li>
                                <li>AOS 动画库</li>
                                <li>CSS3 渐变和动画</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>后端技术</h6>
                            <ul class="small text-muted">
                                <li>Laravel {{ app()->version() }}</li>
                                <li>PHP {{ PHP_VERSION }}</li>
                                <li>Blade 模板引擎</li>
                                <li>MVC 架构模式</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>注意：</strong>这是演示页面，实际首页请访问根路径 <code>/</code>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small>LPadmin 管理系统 - 现代化后台管理解决方案</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .card {
        border: none;
        border-radius: 15px;
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }
    
    .list-group-item {
        border: none;
        padding: 0.5rem 0;
    }
    
    .btn {
        border-radius: 25px;
        font-weight: 600;
    }
    
    .alert {
        border-radius: 10px;
    }
</style>
@endsection
