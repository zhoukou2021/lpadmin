<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LPadmin 仪表盘</title>
    <link rel="stylesheet" href="/static/admin/component/layui/css/layui.css" />
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
</head>
<body>
<style>
/* 仪表盘样式 */
.dashboard-container { padding: 15px; }
.stat-card {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    position: relative;
    transition: box-shadow 0.3s ease;
}
.stat-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.stat-card .stat-icon {
    font-size: 32px;
    color: var(--theme-color, #1E9FFF);
    float: right;
    margin-top: -5px;
}
.stat-card .stat-number {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 3px;
    color: #333;
}
.stat-card .stat-label {
    font-size: 12px;
    color: #666;
}

.system-info-card {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 6px;
    margin-bottom: 10px;
}
.system-info-card .layui-card-header {
    background: #f8f9fa;
    color: #333;
    border-radius: 6px 6px 0 0;
    font-weight: bold;
    font-size: 14px;
    padding: 10px 15px;
}
.system-info-card .layui-card-header i {
    color: var(--theme-color, #1E9FFF);
    margin-right: 5px;
}
.info-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 12px;
}
.info-item:last-child { border-bottom: none; }
.info-label { color: #666; }
.info-value { color: #333; font-weight: 500; }

.module-card {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    padding: 8px 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 6px;
}
.module-card:hover {
    border-color: #1E9FFF;
    background: #f8f9ff;
}
.module-card .module-icon {
    font-size: 16px;
    color: var(--theme-color, #1E9FFF);
    margin-right: 8px;
    vertical-align: middle;
}
.module-card .module-title {
    font-size: 12px;
    font-weight: bold;
    color: #333;
    vertical-align: middle;
}
.module-card .module-desc {
    font-size: 11px;
    color: #999;
    margin-top: 3px;
    margin-left: 24px;
}

.doc-card {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
}
.doc-card:hover {
    border-color: #1E9FFF;
    background: #f8f9ff;
}
.doc-card .doc-icon {
    font-size: 16px;
    color: var(--theme-color, #1E9FFF);
    margin-right: 8px;
    vertical-align: middle;
}
.doc-card .doc-title {
    font-size: 12px;
    font-weight: bold;
    color: #333;
    vertical-align: middle;
}
.doc-card .doc-desc {
    font-size: 11px;
    color: #999;
    margin-top: 3px;
    margin-left: 24px;
}

.chart-container {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
}
.chart-container .chart-title {
    font-size: 14px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    text-align: center;
}
.chart-container .chart-title i {
    color: var(--theme-color, #1E9FFF);
    margin-right: 5px;
}

.welcome-banner {
    background: var(--theme-color, #1E9FFF);
    color: white;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 15px;
    text-align: center;
}
.welcome-banner h1 {
    font-size: 22px;
    margin-bottom: 8px;
    font-weight: bold;
}
.welcome-banner h1 i {
    margin-right: 8px;
}
.welcome-banner p {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 0;
}
</style>
<div class="pear-container dashboard-container">
    <!-- 欢迎横幅 -->
    <div class="welcome-banner">
        <h1><i class="layui-icon layui-icon-home"></i> 欢迎使用 LPadmin 1.0.1</h1>
        <p>基于Laravel 10+和PearAdminLayui构建的现代化后台管理系统 - 让管理更简单，让开发更高效</p>
    </div>

    <!-- 系统统计卡片 -->
    <div class="layui-row layui-col-space10">
        <div class="layui-col-xs6 layui-col-md3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="layui-icon layui-icon-user"></i>
                </div>
                <div class="stat-number" id="admin-count">{{ $statistics['system']['admin_count'] ?? 0 }}</div>
                <div class="stat-label">管理员数量</div>
            </div>
        </div>
        <div class="layui-col-xs6 layui-col-md3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="layui-icon layui-icon-group"></i>
                </div>
                <div class="stat-number" id="user-count">{{ $statistics['system']['user_count'] ?? 0 }}</div>
                <div class="stat-label">用户数量</div>
            </div>
        </div>
        <div class="layui-col-xs6 layui-col-md3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="layui-icon layui-icon-set"></i>
                </div>
                <div class="stat-number" id="role-count">{{ $statistics['system']['role_count'] ?? 0 }}</div>
                <div class="stat-label">角色数量</div>
            </div>
        </div>
        <div class="layui-col-xs6 layui-col-md3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="layui-icon layui-icon-list"></i>
                </div>
                <div class="stat-number" id="rule-count">{{ $statistics['system']['rule_count'] ?? 0 }}</div>
                <div class="stat-label">权限数量</div>
            </div>
        </div>
    </div>

    <!-- 系统信息和开发环境 -->
    <div class="layui-row layui-col-space10">
        <div class="layui-col-md4">
            <div class="system-info-card layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-engine"></i> 系统信息
                </div>
                <div class="layui-card-body" style="padding: 10px 15px;">
                    <div class="info-item">
                        <span class="info-label">系统名称</span>
                        <span class="info-value">LPadmin</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Laravel版本</span>
                        <span class="info-value">{{ app()->version() }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">PHP版本</span>
                        <span class="info-value">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">操作系统</span>
                        <span class="info-value">{{ PHP_OS }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">运行环境</span>
                        <span class="info-value">{{ app()->environment() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md4">
            <div class="system-info-card layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-component"></i> 开发环境
                </div>
                <div class="layui-card-body" style="padding: 10px 15px;">
                    <div class="info-item">
                        <span class="info-label">数据库</span>
                        <span class="info-value">MySQL</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">缓存驱动</span>
                        <span class="info-value">{{ config('cache.default') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">队列驱动</span>
                        <span class="info-value">{{ config('queue.default') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">会话驱动</span>
                        <span class="info-value">{{ config('session.driver') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">调试模式</span>
                        <span class="info-value">{{ config('app.debug') ? '开启' : '关闭' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md4">
            <div class="system-info-card layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-component"></i> 引用扩展
                </div>
                <div class="layui-card-body" style="padding: 10px 15px;">
                    <div class="info-item">
                        <span class="info-label">前端框架</span>
                        <span class="info-value">PearAdminLayui</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">UI组件</span>
                        <span class="info-value">Layui 2.8+</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">图表组件</span>
                        <span class="info-value">ECharts 5.0+</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">HTTP客户端</span>
                        <span class="info-value">Guzzle HTTP</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">认证组件</span>
                        <span class="info-value">Laravel Sanctum</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="layui-row layui-col-space10">
        <!-- 系统核心模块 -->
        <div class="layui-col-md6">
            <div class="system-info-card layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-app"></i> 系统核心模块
                </div>
                <div class="layui-card-body" style="padding: 10px;">
                    <div class="layui-row layui-col-space8">
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/admin')"
                                <i class="layui-icon layui-icon-user module-icon"></i>
                                <span class="module-title">权限管理</span>
                                <div class="module-desc">管理员、角色、权限</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/user')"
                                <i class="layui-icon layui-icon-group module-icon"></i>
                                <span class="module-title">用户管理</span>
                                <div class="module-desc">前台用户管理</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/upload')"
                                <i class="layui-icon layui-icon-upload module-icon"></i>
                                <span class="module-title">文件管理</span>
                                <div class="module-desc">文件上传、附件</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/config')">
                                <i class="layui-icon layui-icon-set module-icon"></i>
                                <span class="module-title">系统配置</span>
                                <div class="module-desc">系统参数配置</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/menu')">
                                <i class="layui-icon layui-icon-list module-icon"></i>
                                <span class="module-title">菜单管理</span>
                                <div class="module-desc">系统菜单配置</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/dictionary')">
                                <i class="layui-icon layui-icon-template module-icon"></i>
                                <span class="module-title">字典管理</span>
                                <div class="module-desc">数据字典管理</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/cache')">
                                <i class="layui-icon layui-icon-engine module-icon"></i>
                                <span class="module-title">缓存管理</span>
                                <div class="module-desc">缓存配置监控</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="module-card" onclick="openModule('{{ lpadmin_url_prefix() }}/log')"
                                <i class="layui-icon layui-icon-log module-icon"></i>
                                <span class="module-title">日志管理</span>
                                <div class="module-desc">系统操作日志</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 开发文档入口 -->
        <div class="layui-col-md6">
            <div class="system-info-card layui-card">
                <div class="layui-card-header">
                    <i class="layui-icon layui-icon-read"></i> 核心开发文档
                </div>
                <div class="layui-card-body" style="padding: 8px 15px;">
                    <div class="layui-row layui-col-space8">
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('README.md')">
                                <i class="layui-icon layui-icon-file doc-icon"></i>
                                <span class="doc-title">项目介绍</span>
                                <div class="doc-desc">系统简介、特性说明</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('INSTALL.md')">
                                <i class="layui-icon layui-icon-download-circle doc-icon"></i>
                                <span class="doc-title">安装指南</span>
                                <div class="doc-desc">环境要求、安装步骤</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('DEVELOPMENT.md')">
                                <i class="layui-icon layui-icon-code-circle doc-icon"></i>
                                <span class="doc-title">开发文档</span>
                                <div class="doc-desc">开发规范、代码结构</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('API.md')">
                                <i class="layui-icon layui-icon-link doc-icon"></i>
                                <span class="doc-title">API接口</span>
                                <div class="doc-desc">接口文档、参数说明</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('DEPLOYMENT.md')">
                                <i class="layui-icon layui-icon-release doc-icon"></i>
                                <span class="doc-title">部署指南</span>
                                <div class="doc-desc">生产环境部署</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('QUICKSTART.md')">
                                <i class="layui-icon layui-icon-play doc-icon"></i>
                                <span class="doc-title">快速开始</span>
                                <div class="doc-desc">快速上手指南</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('CHANGELOG.md')">
                                <i class="layui-icon layui-icon-log doc-icon"></i>
                                <span class="doc-title">更新日志</span>
                                <div class="doc-desc">版本更新记录</div>
                            </div>
                        </div>
                        <div class="layui-col-xs6 layui-col-md3">
                            <div class="doc-card" onclick="openDoc('architecture/database-design.md')">
                                <i class="layui-icon layui-icon-template-1 doc-icon"></i>
                                <span class="doc-title">数据库设计</span>
                                <div class="doc-desc">数据表结构设计</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 数据趋势图表 -->
    <div class="layui-row layui-col-space10">
        <div class="layui-col-md8">
            <div class="chart-container">
                <div class="chart-title">
                    <i class="layui-icon layui-icon-chart"></i> 系统访问趋势
                </div>
                <div id="trend-chart" style="height: 250px;"></div>
            </div>
        </div>
        <div class="layui-col-md4">
            <div class="chart-container">
                <div class="chart-title">
                    <i class="layui-icon layui-icon-chart-screen"></i> 模块使用分布
                </div>
                <div id="pie-chart" style="height: 250px;"></div>
            </div>
        </div>
    </div>

</div>

<script src="/static/admin/component/layui/layui.js"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>
layui.use(['layer', 'element', 'echarts', 'count', 'jquery'], function() {
    const layer = layui.layer;
    const element = layui.element;
    const echarts = layui.echarts;
    const count = layui.count;
    const $ = layui.jquery;

    // CSRF Token配置
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 数字动画效果
    count.up("admin-count", {
        time: 2000,
        num: {{ $statistics['system']['admin_count'] ?? 0 }},
        bit: 0,
        regulator: 10
    });

    count.up("user-count", {
        time: 2000,
        num: {{ $statistics['system']['user_count'] ?? 0 }},
        bit: 0,
        regulator: 50
    });

    count.up("role-count", {
        time: 2000,
        num: {{ $statistics['system']['role_count'] ?? 0 }},
        bit: 0,
        regulator: 5
    });

    count.up("rule-count", {
        time: 2000,
        num: {{ $statistics['system']['rule_count'] ?? 0 }},
        bit: 0,
        regulator: 10
    });

    // 初始化趋势图表
    const trendChart = echarts.init(document.getElementById('trend-chart'));
    const trendOption = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross',
                label: {
                    backgroundColor: '#6a7985'
                }
            }
        },
        legend: {
            data: ['访问量', '用户数', '操作数']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: '访问量',
                type: 'line',
                stack: '总量',
                smooth: true,
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0, color: 'rgba(102, 126, 234, 0.8)'
                        }, {
                            offset: 1, color: 'rgba(102, 126, 234, 0.1)'
                        }]
                    }
                },
                data: [120, 132, 101, 134, 90, 230, 210]
            },
            {
                name: '用户数',
                type: 'line',
                stack: '总量',
                smooth: true,
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0, color: 'rgba(245, 87, 108, 0.8)'
                        }, {
                            offset: 1, color: 'rgba(245, 87, 108, 0.1)'
                        }]
                    }
                },
                data: [220, 182, 191, 234, 290, 330, 310]
            },
            {
                name: '操作数',
                type: 'line',
                stack: '总量',
                smooth: true,
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0,
                        y: 0,
                        x2: 0,
                        y2: 1,
                        colorStops: [{
                            offset: 0, color: 'rgba(67, 233, 123, 0.8)'
                        }, {
                            offset: 1, color: 'rgba(67, 233, 123, 0.1)'
                        }]
                    }
                },
                data: [150, 232, 201, 154, 190, 330, 410]
            }
        ]
    };
    trendChart.setOption(trendOption);

    // 初始化饼图
    const pieChart = echarts.init(document.getElementById('pie-chart'));
    const pieOption = {
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: 'vertical',
            left: 'left'
        },
        series: [
            {
                name: '模块使用',
                type: 'pie',
                radius: '50%',
                data: [
                    { value: 1048, name: '用户管理' },
                    { value: 735, name: '权限管理' },
                    { value: 580, name: '文件管理' },
                    { value: 484, name: '系统配置' },
                    { value: 300, name: '其他模块' }
                ],
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };
    pieChart.setOption(pieOption);

    // 响应式图表
    window.addEventListener('resize', function() {
        trendChart.resize();
        pieChart.resize();
    });
});

// 打开模块
function openModule(url) {
    const index = parent.layer.open({
        type: 2,
        title: '模块管理',
        content: url,
        area: ['90%', '90%'],
        maxmin: true
    });
    parent.layer.full(index);
}

// 打开文档
function openDoc(docPath) {
    const index = parent.layer.open({
        type: 2,
        title: '开发文档 - ' + docPath,
        content: '{{ lpadmin_url_prefix() }}/doc/view?file=' + encodeURIComponent(docPath),
        area: ['90%', '90%'],
        maxmin: true
    });
    parent.layer.full(index);
}
</script>
</body>
</html>
