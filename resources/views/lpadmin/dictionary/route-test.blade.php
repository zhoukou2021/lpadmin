<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <title>数据字典路由测试</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
</head>
<body>
    <div class="layui-container" style="padding: 20px;">
        <h2>数据字典路由测试</h2>
        
        <div class="layui-card">
            <div class="layui-card-header">字典管理路由</div>
            <div class="layui-card-body">
                <table class="layui-table">
                    <thead>
                        <tr>
                            <th>路由名称</th>
                            <th>路由地址</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>dictionary.index</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.index') }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.index') }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                        <tr>
                            <td>dictionary.batch_destroy</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.batch_destroy') }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.batch_destroy') }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                        <tr>
                            <td>dictionary.statistics</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.statistics') }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.statistics') }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                        <tr>
                            <td>dictionary.toggle_status</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.toggle_status', 1) }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.toggle_status', 1) }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="layui-card">
            <div class="layui-card-header">字典项管理路由</div>
            <div class="layui-card-body">
                <table class="layui-table">
                    <thead>
                        <tr>
                            <th>路由名称</th>
                            <th>路由地址</th>
                            <th>状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>dictionary.items.index</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.items.index', 1) }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.items.index', 1) }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                        <tr>
                            <td>dictionary.items.batch_destroy</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.items.batch_destroy', 1) }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.items.batch_destroy', 1) }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                        <tr>
                            <td>dictionary.items.toggle_status</td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.items.toggle_status', ['dictionary' => 1, 'item' => 1]) }}
                                @catch(Exception $e)
                                    <span style="color: red;">错误: {{ $e->getMessage() }}</span>
                                @endtry
                            </td>
                            <td>
                                @try
                                    {{ route('lpadmin.dictionary.items.toggle_status', ['dictionary' => 1, 'item' => 1]) }}
                                    <span style="color: green;">✓</span>
                                @catch(Exception $e)
                                    <span style="color: red;">✗</span>
                                @endtry
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="layui-card">
            <div class="layui-card-header">快速访问</div>
            <div class="layui-card-body">
                <a href="{{ route('lpadmin.dictionary.index') }}" class="layui-btn">字典管理</a>
                <a href="{{ route('lpadmin.dictionary.create') }}" class="layui-btn layui-btn-normal">新增字典</a>
            </div>
        </div>
    </div>
</body>
</html>
