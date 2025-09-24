<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>缓存监控</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/admin.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body class="pear-container">

<!-- 页面标题和操作按钮 -->
<div class="layui-card">
    <div class="layui-card-header">
        <i class="layui-icon layui-icon-chart"></i>
        缓存监控
        <div class="layui-card-header-right">
            <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" id="refresh-monitor">
                <i class="layui-icon layui-icon-refresh"></i>
                刷新数据
            </button>
            <button type="button" class="pear-btn pear-btn-normal pear-btn-sm" id="auto-refresh">
                <i class="layui-icon layui-icon-play"></i>
                自动刷新
            </button>
        </div>
    </div>
    <div class="layui-card-body">
        <!-- 实时统计 -->
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md3">
                <div class="layui-card cache-stat-card">
                    <div class="layui-card-body">
                        <div class="cache-stat-item">
                            <div class="stat-icon hit-rate-icon">
                                <i class="layui-icon layui-icon-rate"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="hit-rate">--</div>
                                <div class="stat-label">命中率</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-card cache-stat-card">
                    <div class="layui-card-body">
                        <div class="cache-stat-item">
                            <div class="stat-icon response-time-icon">
                                <i class="layui-icon layui-icon-time"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="avg-response">--</div>
                                <div class="stat-label">平均响应时间</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-card cache-stat-card">
                    <div class="layui-card-body">
                        <div class="cache-stat-item">
                            <div class="stat-icon memory-icon">
                                <i class="layui-icon layui-icon-upload"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="memory-usage">--</div>
                                <div class="stat-label">内存使用</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-card cache-stat-card">
                    <div class="layui-card-body">
                        <div class="cache-stat-item">
                            <div class="stat-icon keys-icon">
                                <i class="layui-icon layui-icon-key"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-keys">--</div>
                                <div class="stat-label">缓存键数量</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- 图表区域 -->
<div class="layui-row layui-col-space15" style="margin-top: 15px;">
    <div class="layui-col-md6">
        <div class="layui-card">
            <div class="layui-card-header">
                <i class="layui-icon layui-icon-chart-screen"></i>
                命中率趋势
            </div>
            <div class="layui-card-body">
                <div id="hit-rate-chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="layui-col-md6">
        <div class="layui-card">
            <div class="layui-card-header">
                <i class="layui-icon layui-icon-chart"></i>
                响应时间趋势
            </div>
            <div class="layui-card-body">
                <div id="response-time-chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- 缓存键管理 -->
<div class="layui-card" style="margin-top: 15px;">
    <div class="layui-card-header">
        <i class="layui-icon layui-icon-key"></i>
        缓存键管理
        <div class="layui-card-header-right">
            <div class="layui-input-inline" style="width: 200px;">
                <input type="text" id="key-search" placeholder="搜索缓存键..." class="layui-input">
            </div>
            <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" id="search-keys">
                <i class="layui-icon layui-icon-search"></i>
                搜索
            </button>
        </div>
    </div>
    <div class="layui-card-body">
        <table id="cache-keys-table" lay-filter="cache-keys-table"></table>
    </div>
</div>

<style>
/* 统计卡片样式 */
.cache-stat-card {
    transition: all 0.3s ease;
    border: 1px solid #e6e6e6;
}

.cache-stat-card:hover {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.cache-stat-item {
    display: flex;
    align-items: center;
    padding: 20px 0;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
}

.stat-icon i {
    font-size: 28px;
    color: white;
}

.hit-rate-icon {
    background: linear-gradient(135deg, #5470c6, #91cc75);
}

.response-time-icon {
    background: linear-gradient(135deg, #fac858, #ee6666);
}

.memory-icon {
    background: linear-gradient(135deg, #73c0de, #3ba272);
}

.keys-icon {
    background: linear-gradient(135deg, #fc8452, #9a60b4);
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    line-height: 1.2;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    font-weight: normal;
}

/* 卡片头部样式 */
.layui-card-header {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.layui-card-header i {
    margin-right: 8px;
}

.layui-card-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.layui-card-header-right .layui-input-inline {
    margin: 0;
}

/* 图表容器样式 */
#hit-rate-chart, #response-time-chart {
    width: 100%;
    height: 300px;
}

/* 表格样式调整 */
.layui-table {
    margin-top: 0;
}

.layui-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

/* 响应式调整 */
@media (max-width: 768px) {
    .cache-stat-item {
        padding: 15px 0;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        margin-right: 15px;
    }

    .stat-icon i {
        font-size: 24px;
    }

    .stat-value {
        font-size: 24px;
    }

    .layui-card-header-right {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<script src="/static/admin/component/layui/layui.js"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>
layui.use(['layer', 'table', 'form', 'echarts', 'jquery'], function(){
    const layer = layui.layer;
    const table = layui.table;
    const form = layui.form;
    const echarts = layui.echarts;
    const $ = layui.jquery;

    let autoRefreshTimer = null;
    let hitRateChart = null;
    let responseTimeChart = null;

    // 设置CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 初始化
    $(document).ready(function() {
        initCharts();
        initTable();
        loadMonitorData();
        loadCacheKeys();
    });

    // 初始化表格
    function initTable() {
        table.render({
            elem: '#cache-keys-table',
            url: '{{ route("lpadmin.cache.keys") }}',
            method: 'GET',
            page: true,
            limit: 20,
            limits: [10, 20, 50, 100],
            height: 'full-100',
            cols: [[
                {field: 'name', title: '缓存键', width: 400, templet: function(d) {
                    return '<span style="word-break: break-all;">' + (d.name || 'N/A') + '</span>';
                }},
                {field: 'type', title: '类型', align: 'center', templet: function(d) {
                    return d.type || 'N/A';
                }},
                {field: 'size', title: '大小', align: 'center', templet: function(d) {
                    return d.size || 'N/A';
                }},
                {field: 'ttl', title: 'TTL', align: 'center', templet: function(d) {
                    return d.ttl || 'N/A';
                }},
                {field: 'created_at', title: '创建时间', align: 'center', templet: function(d) {
                    return d.created_at || 'N/A';
                }},
                {title: '操作', width: 150, align: 'center', toolbar: '#cache-key-toolbar','fixed': 'right'}
            ]],
            text: {
                none: '暂无缓存键数据'
            },
            response: {
                statusName: 'code',
                statusCode: 0,
                msgName: 'msg',
                countName: 'count',
                dataName: 'data'
            },
            request: {
                pageName: 'page',
                limitName: 'limit'
            }
        });

        // 监听工具条
        table.on('tool(cache-keys-table)', function(obj) {
            const data = obj.data;
            if (obj.event === 'view') {
                viewCacheValue(data.name);
            } else if (obj.event === 'delete') {
                deleteCacheKey(data.name);
            }
        });
    }

    // 初始化图表
    function initCharts() {
        // 命中率趋势图
        hitRateChart = echarts.init(document.getElementById('hit-rate-chart'));
        const hitRateOption = {
            tooltip: {
                trigger: 'axis',
                backgroundColor: 'rgba(50,50,50,0.8)',
                textStyle: { color: '#fff' }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: [],
                axisLine: { lineStyle: { color: '#e6e6e6' } },
                axisTick: { show: false }
            },
            yAxis: {
                type: 'value',
                max: 100,
                axisLabel: { formatter: '{value}%' },
                axisLine: { show: false },
                splitLine: { lineStyle: { color: '#f0f0f0' } }
            },
            series: [{
                name: '命中率',
                type: 'line',
                data: [],
                smooth: true,
                symbol: 'circle',
                symbolSize: 6,
                lineStyle: { width: 3 },
                itemStyle: { color: '#5470c6' },
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0, y: 0, x2: 0, y2: 1,
                        colorStops: [
                            { offset: 0, color: 'rgba(84, 112, 198, 0.3)' },
                            { offset: 1, color: 'rgba(84, 112, 198, 0.1)' }
                        ]
                    }
                }
            }]
        };
        hitRateChart.setOption(hitRateOption);

        // 响应时间趋势图
        responseTimeChart = echarts.init(document.getElementById('response-time-chart'));
        const responseTimeOption = {
            tooltip: {
                trigger: 'axis',
                backgroundColor: 'rgba(50,50,50,0.8)',
                textStyle: { color: '#fff' }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: [],
                axisLine: { lineStyle: { color: '#e6e6e6' } },
                axisTick: { show: false }
            },
            yAxis: {
                type: 'value',
                axisLabel: { formatter: '{value}ms' },
                axisLine: { show: false },
                splitLine: { lineStyle: { color: '#f0f0f0' } }
            },
            series: [{
                name: '响应时间',
                type: 'line',
                data: [],
                smooth: true,
                symbol: 'circle',
                symbolSize: 6,
                lineStyle: { width: 3 },
                itemStyle: { color: '#91cc75' },
                areaStyle: {
                    color: {
                        type: 'linear',
                        x: 0, y: 0, x2: 0, y2: 1,
                        colorStops: [
                            { offset: 0, color: 'rgba(145, 204, 117, 0.3)' },
                            { offset: 1, color: 'rgba(145, 204, 117, 0.1)' }
                        ]
                    }
                }
            }]
        };
        responseTimeChart.setOption(responseTimeOption);
    }

    // 加载监控数据
    function loadMonitorData() {
        $.get('{{ route("lpadmin.cache.monitorData") }}', function(res) {
            if (res.code === 0) {
                updateMonitorStats(res.data.stats);
                updateCharts(res.data.trends);
            } else {
                layer.msg('获取监控数据失败: ' + res.message, {icon: 2});
            }
        }).fail(function() {
            layer.msg('获取监控数据失败', {icon: 2});
        });
    }

    // 更新监控统计
    function updateMonitorStats(stats) {
        $('#hit-rate').text(stats.hit_rate + '%');
        $('#avg-response').text(stats.avg_response + 'ms');
        $('#memory-usage').text(stats.memory_usage);
        $('#total-keys').text(stats.total_keys);
    }

    // 更新图表
    function updateCharts(trends) {
        // 更新命中率图表
        hitRateChart.setOption({
            xAxis: { data: trends.times },
            series: [{ data: trends.hit_rates }]
        });

        // 更新响应时间图表
        responseTimeChart.setOption({
            xAxis: { data: trends.times },
            series: [{ data: trends.response_times }]
        });
    }

    // 加载缓存键列表
    function loadCacheKeys(pattern = '*') {
        table.reload('cache-keys-table', {
            where: {
                pattern: pattern
            },
            page: {
                curr: 1 // 重新从第1页开始
            }
        });
    }

    // 事件绑定
    $('#refresh-monitor').click(function() {
        layer.msg('正在刷新数据...', {icon: 16, time: 1000});
        loadMonitorData();
        loadCacheKeys();
    });

    $('#auto-refresh').click(function() {
        const btn = $(this);
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
            btn.html('<i class="layui-icon layui-icon-play"></i> 自动刷新');
            btn.removeClass('pear-btn-danger').addClass('pear-btn-normal');
            layer.msg('已停止自动刷新', {icon: 1, time: 1000});
        } else {
            autoRefreshTimer = setInterval(function() {
                loadMonitorData();
            }, 5000);
            btn.html('<i class="layui-icon layui-icon-pause"></i> 停止刷新');
            btn.removeClass('pear-btn-normal').addClass('pear-btn-danger');
            layer.msg('已开启自动刷新', {icon: 1, time: 1000});
        }
    });

    $('#search-keys').click(function() {
        const pattern = $('#key-search').val() || '*';
        loadCacheKeys(pattern);
    });

    $('#key-search').keypress(function(e) {
        if (e.which === 13) {
            $('#search-keys').click();
        }
    });

    // 全局函数
    window.viewCacheValue = function(key) {
        $.get('{{ route("lpadmin.cache.getValue") }}', { key: key }, function(res) {
            if (res.code === 0) {
                layer.open({
                    type: 1,
                    title: '缓存值: ' + key,
                    area: ['700px', '500px'],
                    content: '<div style="padding: 20px;"><pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 400px; overflow-y: auto; font-family: Consolas, Monaco, monospace;">' + JSON.stringify(res.data.value, null, 2) + '</pre></div>',
                    shadeClose: true
                });
            } else {
                layer.msg('获取缓存值失败: ' + res.message, {icon: 2});
            }
        });
    };

    window.deleteCacheKey = function(key) {
        layer.confirm('确定要删除缓存键 "' + key + '" 吗？', {
            icon: 3,
            title: '确认删除',
            btn: ['确定', '取消']
        }, function(index) {
            $.ajax({
                url: '{{ route("lpadmin.cache.deleteKey") }}',
                type: 'DELETE',
                data: {
                    key: key,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    layer.close(index);
                    if (res.code === 0) {
                        layer.msg('删除成功', {icon: 1});
                        loadCacheKeys();
                    } else {
                        layer.msg('删除失败: ' + res.message, {icon: 2});
                    }
                },
                error: function() {
                    layer.close(index);
                    layer.msg('删除失败', {icon: 2});
                }
            });
        });
    };
});
</script>

<!-- 表格工具栏模板 -->
<script type="text/html" id="cache-key-toolbar">
    <a class="layui-btn layui-btn-xs" lay-event="view">查看</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
</script>

</body>
</html>
