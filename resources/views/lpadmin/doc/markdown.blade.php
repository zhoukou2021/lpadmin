<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - LPadmin 文档</title>
    <link rel="stylesheet" href="/static/admin/component/layui/css/layui.css" />
    <link rel="stylesheet" href="/static/admin/component/pear/css/pear.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .doc-container {
            display: flex;
            height: 100vh;
            background: #fff;
        }
        
        /* 左侧边栏 */
        .doc-sidebar {
            width: 300px;
            background: #f8f9fa;
            border-right: 1px solid #e6e6e6;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        /* 右侧主内容 */
        .doc-main {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }
        
        /* 侧边栏头部 */
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e6e6e6;
            background: #fff;
        }
        .sidebar-header .logo {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .sidebar-header .logo img {
            height: 32px;
            margin-right: 10px;
        }
        .sidebar-header .logo h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .sidebar-header .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .sidebar-header .doc-meta {
            font-size: 12px;
            color: #999;
        }
        
        /* 工具栏 */
        .doc-toolbar {
            padding: 15px 20px;
            border-bottom: 1px solid #e6e6e6;
        }
        .doc-toolbar .layui-btn {
            margin-right: 8px;
            margin-bottom: 5px;
        }
        
        /* 目录 */
        .doc-toc {
            padding: 20px;
        }
        .doc-toc h4 {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #333;
            font-weight: bold;
        }
        .doc-toc ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .doc-toc li {
            margin-bottom: 8px;
        }
        .doc-toc a {
            color: #666;
            text-decoration: none;
            font-size: 13px;
            display: block;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .doc-toc a:hover {
            color: #1E9FFF;
            background: #f0f8ff;
        }
        .doc-toc a.active {
            color: #1E9FFF;
            background: #e6f4ff;
            font-weight: bold;
        }
        
        /* 主内容区域 */
        .doc-content {
            padding: 30px 40px;
            line-height: 1.8;
            color: #333;
            max-width: 900px;
        }
        
        /* 标题样式 */
        .doc-content h1, .doc-content h2, .doc-content h3, 
        .doc-content h4, .doc-content h5, .doc-content h6 {
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: bold;
            color: #333;
            line-height: 1.4;
        }
        .doc-content h1 { 
            font-size: 32px; 
            border-bottom: 3px solid #1E9FFF; 
            padding-bottom: 15px; 
            margin-top: 0;
        }
        .doc-content h2 { 
            font-size: 26px; 
            border-bottom: 2px solid #e6e6e6; 
            padding-bottom: 10px; 
        }
        .doc-content h3 { font-size: 22px; }
        .doc-content h4 { font-size: 18px; }
        .doc-content h5 { font-size: 16px; }
        .doc-content h6 { font-size: 14px; }
        
        /* 段落和文本 */
        .doc-content p {
            margin-bottom: 16px;
            text-align: justify;
        }
        
        /* 列表 */
        .doc-content ul, .doc-content ol {
            margin-bottom: 16px;
            padding-left: 30px;
        }
        .doc-content li {
            margin-bottom: 8px;
        }
        
        /* 代码 */
        .doc-content code {
            background: #f6f8fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 13px;
            color: #e83e8c;
        }
        .doc-content pre {
            background: #f6f8fa;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
            overflow-x: auto;
            margin-bottom: 16px;
        }
        .doc-content pre code {
            background: none;
            padding: 0;
            color: #333;
        }
        
        /* 引用 */
        .doc-content blockquote {
            border-left: 4px solid #1E9FFF;
            padding: 10px 20px;
            margin: 16px 0;
            background: #f8f9fa;
            color: #666;
        }
        
        /* 表格 */
        .doc-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .doc-content th, .doc-content td {
            border: 1px solid #e6e6e6;
            padding: 8px 12px;
            text-align: left;
        }
        .doc-content th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        /* 链接 */
        .doc-content a {
            color: #1E9FFF;
            text-decoration: none;
        }
        .doc-content a:hover {
            text-decoration: underline;
        }
        
        /* 图片 */
        .doc-content img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 15px 0;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .doc-container {
                flex-direction: column;
                height: auto;
            }
            .doc-sidebar {
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid #e6e6e6;
            }
            .doc-main {
                height: auto;
            }
            .doc-content {
                padding: 20px;
            }
            .doc-toc {
                max-height: 200px;
                overflow-y: auto;
            }
        }
    </style>
</head>
<body>
    <div class="doc-container">
        <!-- 左侧边栏 -->
        <div class="doc-sidebar">
            <!-- 头部信息 -->
            <div class="sidebar-header">
                <div class="logo">
                    <img src="/static/admin/images/logo.png" alt="LPadmin Logo">
                    <h3>LPadmin</h3>
                </div>
                <div class="doc-title">{{ $title }}</div>
                <div class="doc-meta">
                    <i class="layui-icon layui-icon-file"></i> {{ $file }}
                    <br>
                    <i class="layui-icon layui-icon-time"></i> {{ date('Y-m-d H:i:s') }}
                </div>
            </div>
            
            <!-- 工具栏 -->
            <div class="doc-toolbar">
                <button class="layui-btn layui-btn-xs layui-btn-normal" onclick="printDoc()">
                    <i class="layui-icon layui-icon-print"></i> 打印
                </button>
                <button class="layui-btn layui-btn-xs layui-btn-warm" onclick="downloadDoc()">
                    <i class="layui-icon layui-icon-download-circle"></i> 下载
                </button>
            </div>
            
            <!-- 目录 -->
            <div class="doc-toc">
                <h4><i class="layui-icon layui-icon-list"></i> 文档目录</h4>
                <ul id="toc-list"></ul>
            </div>
        </div>
        
        <!-- 右侧主内容 -->
        <div class="doc-main">
            <div class="doc-content" id="doc-content">
                {!! $content !!}
            </div>
        </div>
    </div>

    <script src="/static/admin/component/layui/layui.js"></script>
    <script>
        layui.use(['layer'], function() {
            const layer = layui.layer;
            
            // 生成目录
            generateToc();
            
            // 代码高亮
            highlightCode();
            
            // 监听滚动事件，高亮当前章节
            window.addEventListener('scroll', updateActiveSection);
        });
        
        // 生成目录
        function generateToc() {
            const content = document.getElementById('doc-content');
            const tocList = document.getElementById('toc-list');
            const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
            
            if (headings.length === 0) return;
            
            headings.forEach((heading, index) => {
                const id = 'heading-' + index;
                heading.id = id;
                
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = '#' + id;
                a.textContent = heading.textContent;
                a.style.paddingLeft = (parseInt(heading.tagName.charAt(1)) - 1) * 15 + 'px';
                
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById(id).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
                
                li.appendChild(a);
                tocList.appendChild(li);
            });
        }
        
        // 更新活动章节
        function updateActiveSection() {
            const headings = document.querySelectorAll('#doc-content h1, #doc-content h2, #doc-content h3, #doc-content h4, #doc-content h5, #doc-content h6');
            const tocLinks = document.querySelectorAll('#toc-list a');
            
            let activeIndex = -1;
            
            headings.forEach((heading, index) => {
                const rect = heading.getBoundingClientRect();
                if (rect.top <= 100) {
                    activeIndex = index;
                }
            });
            
            tocLinks.forEach((link, index) => {
                link.classList.toggle('active', index === activeIndex);
            });
        }
        
        // 打印文档
        function printDoc() {
            window.print();
        }
        
        // 下载文档
        function downloadDoc() {
            const link = document.createElement('a');
            link.href = '/lpadmin/doc/download?file={{ urlencode($file) }}';
            link.download = '{{ $file }}';
            link.click();
        }
        
        // 简单的代码高亮
        function highlightCode() {
            const codeBlocks = document.querySelectorAll('pre code');
            codeBlocks.forEach(block => {
                block.innerHTML = block.innerHTML
                    .replace(/\b(function|var|let|const|if|else|for|while|return|class|extends|public|private|protected)\b/g, '<span style="color: #c678dd;">$1</span>')
                    .replace(/\b(true|false|null|undefined)\b/g, '<span style="color: #56b6c2;">$1</span>')
                    .replace(/"([^"]*)"/g, '<span style="color: #98c379;">"$1"</span>')
                    .replace(/'([^']*)'/g, '<span style="color: #98c379;">\'$1\'</span>')
                    .replace(/\/\*[\s\S]*?\*\//g, '<span style="color: #5c6370; font-style: italic;">$&</span>')
                    .replace(/\/\/.*$/gm, '<span style="color: #5c6370; font-style: italic;">$&</span>');
            });
        }
    </script>
</body>
</html>
