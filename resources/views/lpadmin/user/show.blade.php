<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>查看用户</title>
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <link rel="stylesheet" href="/static/admin/css/reset.css" />
    <link rel="stylesheet" href="/static/admin/css/form-common.css" />
</head>
<body>
    <div class="mainBox">
        <div class="main-container">
            <table class="layui-table" lay-skin="line">
                <colgroup>
                    <col width="150">
                    <col>
                </colgroup>
                <tbody>
                    <tr>
                        <td></td>
                        <td><img src="{{ $user->avatar ?: '/static/images/default-avatar.png' }}" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #e6e6e6; "></td>
                    </tr>
                    <tr>
                        <td>用户名</td>
                        <td>{{ $user->username }}</td>
                    </tr>
                    <tr>
                        <td>昵称</td>
                        <td>{{ $user->nickname }}</td>
                    </tr>
                    <tr>
                        <td>性别</td>
                        <td>{{ $user->gender }}</td>
                    </tr>
                    <tr>
                        <td>邮箱</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td>手机号</td>
                        <td>{{ $user->phone }}</td>
                    </tr>
                    <tr>
                        <td>状态</td>
                        <td>{{ $user->status == 1 ? '启用' : '禁用' }}</td>
                    </tr>
                    <tr>
                        <td>备注</td>
                        <td>{{ $user->remark}}</td>
                    </tr>
                    
                </tbody>
            </table>
        </div>

    </div>    
</body>
</html>
