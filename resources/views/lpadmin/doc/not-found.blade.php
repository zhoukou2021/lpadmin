<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文档未找到 - LPadmin</title>
    <link rel="stylesheet" href="/static/admin/component/layui/css/layui.css" />
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <style>
        .not-found-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 20px;
        }
        .not-found-icon {
            font-size: 120px;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        .not-found-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .not-found-message {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
            max-width: 600px;
            line-height: 1.6;
        }
        .not-found-file {
            background: rgba(255,255,255,0.1);
            padding: 10px 20px;
            border-radius: 25px;
            margin-bottom: 30px;
            font-family: monospace;
            font-size: 16px;
        }
        .not-found-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .action-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .action-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .action-btn i {
            font-size: 18px;
        }
        
        @media (max-width: 768px) {
            .not-found-icon { font-size: 80px; }
            .not-found-title { font-size: 28px; }
            .not-found-message { font-size: 16px; }
            .not-found-actions { flex-direction: column; align-items: center; }
            .action-btn { width: 200px; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="not-found-container">
        <div class="not-found-icon">
            <i class="layui-icon layui-icon-file"></i>
        </div>
        
        <div class="not-found-title">文档未找到</div>
        
        <div class="not-found-message">
            抱歉，您要查看的文档不存在或已被移动。请检查文件路径是否正确，或者从文档列表中选择其他文档。
        </div>
        
        <div class="not-found-file">
            <i class="layui-icon layui-icon-close-fill" style="color: #ff6b6b; margin-right: 8px;"></i>
            {{ $file }}
        </div>
        
        <div class="not-found-actions">
            <a href="/lpadmin/dashboard" class="action-btn">
                <i class="layui-icon layui-icon-home"></i>
                返回首页
            </a>
            <a href="/lpadmin/doc" class="action-btn">
                <i class="layui-icon layui-icon-list"></i>
                文档列表
            </a>
            <a href="javascript:history.back()" class="action-btn">
                <i class="layui-icon layui-icon-return"></i>
                返回上页
            </a>
        </div>
    </div>
</body>
</html>
