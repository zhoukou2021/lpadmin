<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;


/**
 * 文档查看控制器
 *
 * 处理Markdown文档的查看和渲染
 */
class DocController extends BaseController
{
    /**
     * 查看文档
     *
     * @param Request $request
     * @return View|Response
     */
    public function show(Request $request)
    {
        $file = $request->input('file', 'README.md');
        
        // 安全检查：防止路径遍历攻击
        $file = str_replace(['../', '..\\'], '', $file);
        $docPath = base_path('docs/' . $file);
        
        // 检查文件是否存在
        if (!File::exists($docPath)) {
            return response()->view('lpadmin.doc.not-found', [
                'file' => $file
            ], 404);
        }
        
        // 读取文件内容
        $content = File::get($docPath);
        
        // 如果是Markdown文件，进行渲染
        if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
            $htmlContent = $this->parseMarkdown($content);

            return view('lpadmin.doc.markdown', [
                'title' => $this->getDocTitle($file),
                'file' => $file,
                'content' => $htmlContent,
                'rawContent' => $content
            ]);
        }
        
        // 其他文件类型直接返回原始内容
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8'
        ]);
    }
    
    /**
     * 下载文档
     *
     * @param Request $request
     * @return Response
     */
    public function download(Request $request)
    {
        $file = $request->input('file', 'README.md');

        // 安全检查：防止路径遍历攻击
        $file = str_replace(['../', '..\\'], '', $file);
        $docPath = base_path('docs/' . $file);

        // 检查文件是否存在
        if (!File::exists($docPath)) {
            return response()->json([
                'code' => 404,
                'msg' => '文件不存在',
                'data' => null
            ], 404);
        }

        // 返回文件下载
        return response()->download($docPath, $file, [
            'Content-Type' => 'text/markdown; charset=utf-8'
        ]);
    }

    /**
     * 获取文档列表
     *
     * @return View
     */
    public function index(): View
    {
        $docsPath = base_path('docs');
        $docs = $this->getDocumentList($docsPath);

        return view('lpadmin.doc.index', [
            'docs' => $docs
        ]);
    }
    
    /**
     * 获取文档标题
     *
     * @param string $file
     * @return string
     */
    private function getDocTitle(string $file): string
    {
        $titles = [
            'README.md' => '项目介绍',
            'INSTALL.md' => '安装指南',
            'DEVELOPMENT.md' => '开发文档',
            'API.md' => 'API接口',
            'DEPLOYMENT.md' => '部署指南',
            'QUICKSTART.md' => '快速开始',
            'CHANGELOG.md' => '更新日志',
            'DEVELOPMENT-PLAN.md' => '开发计划',
            'architecture/database-design.md' => '数据库设计',
            'architecture/permission-system.md' => '权限系统',
        ];
        
        return $titles[$file] ?? pathinfo($file, PATHINFO_FILENAME);
    }
    
    /**
     * 递归获取文档列表
     *
     * @param string $path
     * @param string $prefix
     * @return array
     */
    private function getDocumentList(string $path, string $prefix = ''): array
    {
        $docs = [];
        
        if (!is_dir($path)) {
            return $docs;
        }
        
        $files = scandir($path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            $relativePath = $prefix . $file;
            
            if (is_dir($fullPath)) {
                $docs[] = [
                    'type' => 'directory',
                    'name' => $file,
                    'path' => $relativePath,
                    'children' => $this->getDocumentList($fullPath, $relativePath . '/')
                ];
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                $docs[] = [
                    'type' => 'file',
                    'name' => $file,
                    'path' => $relativePath,
                    'title' => $this->getDocTitle($relativePath),
                    'size' => filesize($fullPath),
                    'modified' => filemtime($fullPath)
                ];
            }
        }
        
        return $docs;
    }

    /**
     * 简单的Markdown解析
     *
     * @param string $content
     * @return string
     */
    private function parseMarkdown(string $content): string
    {
        // 简单的Markdown解析，支持基本语法
        $html = $content;

        // 标题
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $html);
        $html = preg_replace('/^##### (.+)$/m', '<h5>$1</h5>', $html);
        $html = preg_replace('/^###### (.+)$/m', '<h6>$1</h6>', $html);

        // 粗体和斜体
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);

        // 代码块
        $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code>$2</code></pre>', $html);
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);

        // 链接
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html);

        // 图片
        $html = preg_replace('/!\[(.+?)\]\((.+?)\)/', '<img src="$2" alt="$1">', $html);

        // 列表
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);

        // 引用
        $html = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $html);

        // 段落
        $html = preg_replace('/\n\n/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';

        // 清理多余的段落标签
        $html = preg_replace('/<p><\/p>/', '', $html);
        $html = preg_replace('/<p>(<h[1-6]>)/', '$1', $html);
        $html = preg_replace('/(<\/h[1-6]>)<\/p>/', '$1', $html);
        $html = preg_replace('/<p>(<ul>)/', '$1', $html);
        $html = preg_replace('/(<\/ul>)<\/p>/', '$1', $html);
        $html = preg_replace('/<p>(<blockquote>)/', '$1', $html);
        $html = preg_replace('/(<\/blockquote>)<\/p>/', '$1', $html);
        $html = preg_replace('/<p>(<pre>)/', '$1', $html);
        $html = preg_replace('/(<\/pre>)<\/p>/', '$1', $html);

        return $html;
    }
}
