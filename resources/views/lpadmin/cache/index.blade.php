<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>缓存管理</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/admin.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body class="pear-container">
    <div class="layui-card">
        <div class="layui-card-header">
            <i class="layui-icon layui-icon-engine"></i>
            缓存管理
            <div class="layui-card-header-right">
                <button type="button" class="pear-btn pear-btn-primary pear-btn-sm" id="refresh-stats">
                    <i class="layui-icon layui-icon-refresh"></i>
                    刷新统计
                </button>
                <button type="button" class="pear-btn pear-btn-danger pear-btn-sm" id="clear-all-cache">
                    <i class="layui-icon layui-icon-delete"></i>
                    清除所有缓存
                </button>
            </div>
        </div>
        <div class="layui-card-body">
            <div class="layui-row layui-col-space15" id="cache-stats">
                <!-- 缓存统计卡片将在这里动态生成 -->
            </div>
        </div>
    </div>

<!-- 缓存项模板 -->
<script type="text/html" id="cache-item-tpl">
    <div class="layui-col-md3">
        <div class="layui-card cache-item" data-type="@{{ d.type }}">
            <div class="layui-card-header">
                <i class="layui-icon @{{ d.icon }}"></i>
                @{{ d.name }}
            </div>
            <div class="layui-card-body">
                <div class="cache-stats">
                    <div class="stat-item">
                        <span class="stat-label">大小:</span>
                        <span class="stat-value">@{{ d.size }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">数量:</span>
                        <span class="stat-value">@{{ d.count }}</span>
                    </div>
                </div>
                <div class="cache-description">
                    @{{ d.description }}
                </div>
                <div class="cache-actions">
                    <button type="button" class="pear-btn pear-btn-sm pear-btn-primary clear-cache-btn" data-type="@{{ d.type }}">
                        <i class="layui-icon layui-icon-delete"></i>
                        清除缓存
                    </button>
                </div>
            </div>
        </div>
    </div>
</script>

<style>
.cache-item {
    transition: all 0.3s ease;
    cursor: pointer;
}

.cache-item:hover {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.cache-stats {
    margin: 10px 0;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    margin: 5px 0;
    font-size: 13px;
}

.stat-label {
    color: #666;
}

.stat-value {
    font-weight: bold;
    color: #1890ff;
}

.cache-description {
    color: #999;
    font-size: 12px;
    margin: 10px 0;
    line-height: 1.4;
}

.cache-actions {
    margin-top: 15px;
    text-align: center;
}

.layui-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.layui-card-header-right {
    display: flex;
    gap: 8px;
}

.layui-card-header i {
    margin-right: 8px;
    font-size: 16px;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.cache-item {
    position: relative;
}

.cache-item.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* 自定义加载框样式 */
.layui-layer-hui {
    background: rgba(0, 0, 0, 0.8) !important;
    color: #fff !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}

.layui-layer-hui .layui-layer-content {
    padding: 20px !important;
    text-align: center !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
}

.layui-layer-hui .layui-layer-loading2 {
    border-color: #fff transparent transparent transparent !important;
}

/* 美化加载动画 */
.layui-layer-loading2:after {
    border-color: rgba(255, 255, 255, 0.3) transparent transparent transparent !important;
}

/* 自定义加载框样式 */
.custom-loading-layer {
    background: rgba(255, 255, 255, 0.95) !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important;
    backdrop-filter: blur(10px) !important;
}

.custom-loading-layer .layui-layer-content {
    overflow: hidden !important;
    padding: 0 !important;
}

.custom-loading {
    padding: 25px 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    box-sizing: border-box;
}

.loading-spinner {
    position: relative;
    width: 60px;
    height: 60px;
    margin-bottom: 20px;
}

.spinner-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 3px solid transparent;
    border-radius: 50%;
    animation: spin 1.5s linear infinite;
}

.spinner-ring:nth-child(1) {
    border-top-color: #1890ff;
    animation-delay: 0s;
}

.spinner-ring:nth-child(2) {
    border-top-color: #52c41a;
    animation-delay: -0.5s;
    width: 80%;
    height: 80%;
    top: 10%;
    left: 10%;
}

.spinner-ring:nth-child(3) {
    border-top-color: #faad14;
    animation-delay: -1s;
    width: 60%;
    height: 60%;
    top: 20%;
    left: 20%;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    color: #333;
}

.loading-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #1890ff;
}

.loading-subtitle {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

/* 简化版加载效果 */
.custom-loading.simple {
    padding: 15px 20px;
    box-sizing: border-box;
}

.loading-spinner.simple {
    width: 40px;
    height: 40px;
    margin-bottom: 15px;
}

.loading-spinner.simple .spinner-ring {
    border-width: 2px;
}

.loading-spinner.simple .spinner-ring:nth-child(1) {
    border-top-color: #1890ff;
}
</style>

<!-- JavaScript -->
<script src="/static/admin/component/layui/layui.js?v=2.8.12"></script>
<script src="/static/admin/component/pear/pear.js"></script>
<script>
layui.use(['jquery', 'layer', 'laytpl'], function() {
    const $ = layui.jquery;
    const layer = layui.layer;
    const laytpl = layui.laytpl;

    // 设置CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // API 地址
    const STATS_API = '{{ route("lpadmin.cache.stats") }}';
    const CLEAR_TYPE_API = '{{ route("lpadmin.cache.clearByType") }}';
    const CLEAR_ALL_API = '{{ route("lpadmin.cache.clearAll") }}';

    // 加载缓存统计
    function loadCacheStats() {
        $.get(STATS_API, function(res) {
            if (res.code === 0) {
                renderCacheStats(res.data);
            } else {
                layer.msg('获取缓存统计失败: ' + res.message, {icon: 2});
            }
        }).fail(function() {
            layer.msg('获取缓存统计失败', {icon: 2});
        });
    }

    // 渲染缓存统计
    function renderCacheStats(stats) {
        const container = $('#cache-stats');
        container.empty();

        const tpl = $('#cache-item-tpl').html();
        
        Object.keys(stats).forEach(function(type) {
            const data = stats[type];
            data.type = type;
            
            const html = laytpl(tpl).render(data);
            container.append(html);
        });
    }

    // 清除指定类型缓存
    function clearCacheByType(type, callback) {
        const loadIndex = layer.open({
            type: 1,
            content: `
                <div class="custom-loading simple">
                    <div class="loading-spinner simple">
                        <div class="spinner-ring"></div>
                    </div>
                    <div class="loading-text">
                        <div class="loading-title">正在清除缓存</div>
                    </div>
                </div>
            `,
            shade: [0.4, '#000'],
            shadeClose: false,
            time: 0,
            area: ['260px', '130px'],
            title: false,
            closeBtn: 0,
            skin: 'custom-loading-layer',
            resize: false
        });

        $.post(CLEAR_TYPE_API, {type: type}, function(res) {
            layer.close(loadIndex);

            if (res.code === 0) {
                layer.msg(res.message, {icon: 1, time: 2000});
                if (callback) callback();
            } else {
                layer.msg(res.message, {icon: 2, time: 3000});
            }
        }).fail(function() {
            layer.close(loadIndex);
            layer.msg('清除缓存失败', {icon: 2, time: 3000});
        });
    }

    // 清除所有缓存
    function clearAllCache() {
        layer.confirm('确定要清除所有缓存吗？此操作可能需要一些时间。', {
            icon: 3,
            title: '确认清除'
        }, function(confirmIndex) {
            // 先关闭确认对话框
            layer.close(confirmIndex);

            // 然后显示加载框，确保层次正确
            const loadIndex = layer.open({
                type: 1,
                content: `
                    <div class="custom-loading">
                        <div class="loading-spinner">
                            <div class="spinner-ring"></div>
                            <div class="spinner-ring"></div>
                            <div class="spinner-ring"></div>
                        </div>
                        <div class="loading-text">
                            <div class="loading-title">正在清除所有缓存</div>
                            <div class="loading-subtitle">请稍候，这可能需要一些时间...</div>
                        </div>
                    </div>
                `,
                shade: [0.5, '#000'],
                shadeClose: false,
                time: 0,
                area: ['320px', '180px'],
                title: false,
                closeBtn: 0,
                skin: 'custom-loading-layer',
                resize: false
            });

            $.post(CLEAR_ALL_API, {}, function(res) {
                layer.close(loadIndex);

                if (res.code === 0) {
                    layer.msg(res.message, {icon: 1, time: 2000});
                    loadCacheStats(); // 重新加载统计
                } else {
                    layer.msg(res.message, {icon: 2, time: 3000});
                }
            }).fail(function() {
                layer.close(loadIndex);
                layer.msg('清除缓存失败', {icon: 2, time: 3000});
            });
        });
    }

    // 事件绑定
    $(document).on('click', '.clear-cache-btn', function() {
        const type = $(this).data('type');
        const typeName = $(this).closest('.cache-item').find('.layui-card-header').text().trim();

        layer.confirm('确定要清除 "' + typeName + '" 缓存吗？', {
            icon: 3,
            title: '确认清除'
        }, function(confirmIndex) {
            // 先关闭确认对话框
            layer.close(confirmIndex);

            // 然后执行清除操作
            clearCacheByType(type, function() {
                loadCacheStats(); // 重新加载统计
            });
        });
    });

    $('#refresh-stats').click(function() {
        loadCacheStats();
    });

    $('#clear-all-cache').click(function() {
        clearAllCache();
    });

    // 页面加载时获取统计
    loadCacheStats();
});
</script>
</body>
</html>
