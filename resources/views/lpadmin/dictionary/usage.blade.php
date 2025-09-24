<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>字典使用示例</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/admin.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
</head>
<body class="pear-container">
    <div class="layui-card">
        <div class="layui-card-header">
            <i class="layui-icon layui-icon-list"></i>
            字典使用示例
        </div>
        <div class="layui-card-body">
            <div class="layui-row layui-col-space15">
                <!-- 基础使用示例 -->
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">基础使用示例</div>
                        <div class="layui-card-body">
                            <form class="layui-form" lay-filter="demo-form">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">用户状态</label>
                                    <div class="layui-input-block">
                                        <select name="user_status" lay-verify="required">
                                            <option value="">请选择状态</option>
                                            @if(isset($userStatusDict))
                                                @foreach($userStatusDict as $item)
                                                    <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">用户类型</label>
                                    <div class="layui-input-block">
                                        @if(isset($userTypeDict))
                                            @foreach($userTypeDict as $item)
                                                <input type="radio" name="user_type" value="{{ $item['value'] }}" title="{{ $item['label'] }}">
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <label class="layui-form-label">用户标签</label>
                                    <div class="layui-input-block">
                                        @if(isset($userTagsDict))
                                            @foreach($userTagsDict as $item)
                                                <input type="checkbox" name="user_tags[]" value="{{ $item['value'] }}" title="{{ $item['label'] }}">
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="layui-form-item">
                                    <div class="layui-input-block">
                                        <button class="layui-btn" lay-submit lay-filter="demo-submit">提交</button>
                                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 动态加载示例 -->
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">动态加载示例</div>
                        <div class="layui-card-body">
                            <div class="layui-form-item">
                                <label class="layui-form-label">选择字典</label>
                                <div class="layui-input-block">
                                    <select id="dict-selector">
                                        <option value="">请选择字典</option>
                                        <option value="user_status">用户状态</option>
                                        <option value="user_type">用户类型</option>
                                        <option value="user_tags">用户标签</option>
                                    </select>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <label class="layui-form-label">字典数据</label>
                                <div class="layui-input-block">
                                    <div id="dict-data-display">
                                        <p class="layui-text">请选择字典查看数据</p>
                                    </div>
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button type="button" class="layui-btn layui-btn-sm" id="load-dict-btn">加载字典数据</button>
                                    <button type="button" class="layui-btn layui-btn-warm layui-btn-sm" id="clear-cache-btn">清除缓存</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 字典数据展示 -->
            <div class="layui-row layui-col-space15" style="margin-top: 15px;">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-header">字典数据展示</div>
                        <div class="layui-card-body">
                            <div class="layui-row layui-col-space15">
                                <div class="layui-col-md4">
                                    <h4>用户状态标签</h4>
                                    <div id="status-badges">
                                        <!-- 状态标签将在这里显示 -->
                                    </div>
                                </div>
                                <div class="layui-col-md4">
                                    <h4>用户类型标签</h4>
                                    <div id="type-badges">
                                        <!-- 类型标签将在这里显示 -->
                                    </div>
                                </div>
                                <div class="layui-col-md4">
                                    <h4>用户标签</h4>
                                    <div id="tag-badges">
                                        <!-- 用户标签将在这里显示 -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js"></script>
    <script>
        layui.use(['form', 'layer', 'util'], function(){
            var form = layui.form;
            var layer = layui.layer;
            var util = layui.util;
            var $ = layui.$;

            // 字典数据API地址
            const DICT_DATA_API = "{{ route('lpadmin.dictionary.data') }}";
            const DICT_OPTIONS_API = "{{ route('lpadmin.dictionary.options') }}";
            const CLEAR_CACHE_API = "{{ route('lpadmin.dictionary.clear_cache') }}";

            // 表单提交
            form.on('submit(demo-submit)', function(data){
                layer.msg('提交的数据：' + JSON.stringify(data.field), {time: 3000});
                return false;
            });

            // 字典选择器变化
            $('#dict-selector').on('change', function(){
                var dictName = $(this).val();
                if(dictName){
                    loadDictData(dictName);
                } else {
                    $('#dict-data-display').html('<p class="layui-text">请选择字典查看数据</p>');
                }
            });

            // 加载字典数据按钮
            $('#load-dict-btn').on('click', function(){
                var dictName = $('#dict-selector').val();
                if(!dictName){
                    layer.msg('请先选择字典', {icon: 2});
                    return;
                }
                loadDictData(dictName);
            });

            // 清除缓存按钮
            $('#clear-cache-btn').on('click', function(){
                var dictName = $('#dict-selector').val();
                clearDictCache(dictName);
            });

            // 加载字典数据
            function loadDictData(dictName) {
                var loading = layer.load();
                $.ajax({
                    url: DICT_DATA_API,
                    type: 'GET',
                    data: {
                        name: dictName,
                        enabled_only: true
                    },
                    success: function(res){
                        layer.close(loading);
                        if(res.code === 0){
                            displayDictData(res.data);
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    },
                    error: function(){
                        layer.close(loading);
                        layer.msg('加载失败', {icon: 2});
                    }
                });
            }

            // 显示字典数据
            function displayDictData(data) {
                var html = '<div class="layui-row layui-col-space10">';
                data.forEach(function(item){
                    var colorClass = item.color ? 'layui-bg-' + item.color : 'layui-bg-blue';
                    html += '<div class="layui-col-md3">';
                    html += '<span class="layui-badge ' + colorClass + '">' + item.label + '</span>';
                    html += '<small style="margin-left: 10px;">(' + item.value + ')</small>';
                    html += '</div>';
                });
                html += '</div>';
                $('#dict-data-display').html(html);
            }

            // 清除字典缓存
            function clearDictCache(dictName) {
                var loading = layer.load();
                $.ajax({
                    url: CLEAR_CACHE_API,
                    type: 'POST',
                    data: {
                        name: dictName,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res){
                        layer.close(loading);
                        if(res.code === 0){
                            layer.msg(res.message, {icon: 1});
                        } else {
                            layer.msg(res.message, {icon: 2});
                        }
                    },
                    error: function(){
                        layer.close(loading);
                        layer.msg('清除缓存失败', {icon: 2});
                    }
                });
            }

            // 初始化展示一些示例标签
            initExampleBadges();

            function initExampleBadges() {
                // 用户状态示例
                $('#status-badges').html(`
                    <span class="layui-badge layui-bg-green">正常</span>
                    <span class="layui-badge layui-bg-red">禁用</span>
                    <span class="layui-badge layui-bg-orange">待审核</span>
                `);

                // 用户类型示例
                $('#type-badges').html(`
                    <span class="layui-badge layui-bg-blue">普通用户</span>
                    <span class="layui-badge layui-bg-cyan">VIP用户</span>
                    <span class="layui-badge layui-bg-black">管理员</span>
                `);

                // 用户标签示例
                $('#tag-badges').html(`
                    <span class="layui-badge">新用户</span>
                    <span class="layui-badge layui-bg-gray">活跃用户</span>
                    <span class="layui-badge layui-bg-green">认证用户</span>
                `);
            }
        });
    </script>
</body>
</html>
