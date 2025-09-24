@extends('home.layout')

@section('title', 'LPadmin - 现代化后台管理系统')
@section('description', 'LPadmin是基于Laravel 10+和PearAdminLayui构建的现代化后台管理系统，提供完整的RBAC权限管理、用户管理、系统配置等核心功能')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content" data-aos="fade-right">
                <h1 class="hero-title">{{ $systemInfo['name'] }}</h1>
                <p class="hero-subtitle">{{ $systemInfo['description'] }}</p>
                <div class="hero-buttons">
                    <a href="{{ lpadmin_url_prefix() }}/login" class="btn-hero btn-primary-hero">
                        <i class="fas fa-sign-in-alt me-2"></i>立即体验
                    </a>
                    <a href="#features" class="btn-hero btn-outline-hero">
                        <i class="fas fa-info-circle me-2"></i>了解更多
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="text-center">
                    <div class="admin-screenshot">
                        <img src="{{ asset('static/images/admin.png') }}" alt="LPadmin 后台截图" class="img-fluid screenshot-img">
                        <div class="screenshot-overlay">
                            <div class="play-button">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Features Section -->
<section id="core-features" class="section">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">后台核心功能</h2>
        <div class="row">
            @foreach($coreFeatures as $feature)
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="core-feature-card">
                    <div class="feature-header">
                        <div class="feature-icon-large bg-{{ $feature['color'] }}">
                            <i class="{{ $feature['icon'] }}"></i>
                        </div>
                        <h4 class="feature-title">{{ $feature['title'] }}</h4>
                    </div>
                    <p class="feature-description">{{ $feature['description'] }}</p>
                    <div class="feature-list">
                        @foreach($feature['features'] as $item)
                        <div class="feature-item">
                            <i class="fas fa-check text-{{ $feature['color'] }} me-2"></i>
                            <span>{{ $item }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- System Advantages Section -->
<section id="features" class="section" style="background: var(--light-bg);">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">系统优势</h2>
        <div class="row">
            @foreach($systemInfo['features'] as $title => $description)
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="feature-card">
                    <div class="feature-icon">
                        @switch($title)
                            @case('现代化架构')
                                <i class="fas fa-rocket"></i>
                                @break
                            @case('美观界面')
                                <i class="fas fa-palette"></i>
                                @break
                            @case('权限系统')
                                <i class="fas fa-shield-alt"></i>
                                @break
                            @case('响应式设计')
                                <i class="fas fa-mobile-alt"></i>
                                @break
                            @case('高度可配置')
                                <i class="fas fa-cogs"></i>
                                @break
                            @case('安全可靠')
                                <i class="fas fa-lock"></i>
                                @break
                            @case('易于扩展')
                                <i class="fas fa-puzzle-piece"></i>
                                @break
                            @case('开源免费')
                                <i class="fas fa-heart"></i>
                                @break
                            @default
                                <i class="fas fa-star"></i>
                        @endswitch
                    </div>
                    <h4 class="mb-3">{{ $title }}</h4>
                    <p class="text-muted">{{ $description }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Tech Stack Section -->
<section class="tech-stack section" style="background: white;">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">技术栈</h2>
        <div class="row">
            @foreach($systemInfo['tech_stack'] as $tech => $version)
            <div class="col-lg-4 col-md-6 mb-3" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="tech-item">
                    <strong>{{ $tech }}</strong>
                    <div class="text-muted">{{ $version }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- System Info Section -->
<section class="section" style="background: var(--light-bg);">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">系统信息</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg" data-aos="fade-up">
                    <div class="card-body p-5">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>系统名称：</strong>
                                <span class="text-muted">{{ $systemInfo['name'] }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>系统版本：</strong>
                                <span class="text-muted">{{ $systemInfo['version'] }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Laravel版本：</strong>
                                <span class="text-muted">{{ $systemInfo['laravel_version'] }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>PHP版本：</strong>
                                <span class="text-muted">{{ $systemInfo['php_version'] }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <a href="{{ lpadmin_url_prefix() }}/login" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>进入管理后台
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5>关于 LPadmin</h5>
                <p>LPadmin是一个现代化的后台管理系统，基于Laravel框架开发，提供完整的权限管理和系统配置功能。</p>
            </div>
            <div class="col-lg-4 mb-4">
                <h5>快速链接</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ lpadmin_url_prefix() }}/login"><i class="fas fa-sign-in-alt me-2"></i>管理后台</a></li>
                    <li><a href="#features"><i class="fas fa-star me-2"></i>功能特性</a></li>
                    <li><a href="https://laravel.com" target="_blank"><i class="fas fa-external-link-alt me-2"></i>Laravel官网</a></li>
                    <li><a href="https://github.com" target="_blank"><i class="fab fa-github me-2"></i>GitHub</a></li>
                </ul>
            </div>
            <div class="col-lg-4 mb-4">
                <h5>联系方式</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope me-2"></i>{{ $systemInfo['contact']['email'] }}</li>
                    <li><i class="fas fa-phone me-2"></i>{{ $systemInfo['contact']['phone'] }}</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i>{{ $systemInfo['contact']['address'] }}</li>
                    <li class="mt-3">
                        <a href="{{ $systemInfo['social']['github'] }}" class="me-3" target="_blank"><i class="fab fa-github fa-lg"></i></a>
                        <a href="{{ $systemInfo['social']['qq'] }}" class="me-3" target="_blank"><i class="fab fa-qq fa-lg"></i></a>
                        <a href="{{ $systemInfo['social']['wechat'] }}" class="me-3" target="_blank"><i class="fab fa-weixin fa-lg"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">&copy; {{ date('Y') }} {{ $systemInfo['copyright'] }}. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">Powered by LPadmin Team</p>
            </div>
        </div>
    </div>
</footer>
@endsection
