<?php

namespace App\Http\Controllers\LPadmin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use App\Models\LPadmin\Upload;
use App\Models\LPadmin\Option;

class UploadController extends BaseController
{
    /**
     * 获取上传配置（合并数据库和配置文件）
     */
    private function getUploadConfig(): array
    {
        // 默认配置
        $defaultConfig = config('lpadmin.upload', [
            'disk' => 'public',
            'path' => 'lpadmin/uploads',
            'max_size' => 10240,
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        ]);

        // 从数据库获取配置
        $dbConfig = [
            'disk' => Option::getValue('upload_disk', $defaultConfig['disk']),
            'path' => Option::getValue('upload_path', $defaultConfig['path']),
            'max_size' => (int) Option::getValue('upload_max_size', $defaultConfig['max_size']),
            'allowed_extensions' => json_decode(Option::getValue('allowed_extensions', json_encode($defaultConfig['allowed_extensions'])), true),
            'enable_security_check' => (int) Option::getValue('enable_security_check', true),
            'enable_duplicate_check' => (int) Option::getValue('enable_duplicate_check', true),
        ];

        return array_merge($defaultConfig, $dbConfig);
    }
    /**
     * 显示文件上传页面
     */
    public function create(): View
    {
        return view('lpadmin.upload.create');
    }

    /**
     * 文件管理列表
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Upload::with('admin:id,username,nickname');

            // 搜索条件
            if ($request->filled('original_name')) {
                $query->where('original_name', 'like', '%' . $request->original_name . '%');
            }

            if ($request->filled('type')) {
                $query->ofType($request->type);
            }

            // 分类筛选（检查字段是否存在）
            if ($request->filled('category')) {
                try {
                    $query->where('category', $request->category);
                } catch (\Exception $e) {
                    // 如果 category 字段不存在，忽略此筛选条件
                    if (strpos($e->getMessage(), 'Unknown column') === false) {
                        throw $e;
                    }
                }
            }

            if ($request->filled('extension')) {
                $query->where('extension', $request->extension);
            }

            if ($request->filled('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }

            if ($request->filled('tags')) {
                $query->whereJsonContains('tags', $request->tags);
            }

            // 排序
            $field = $request->get('field', 'created_at');
            $order = $request->get('order', 'desc');
            $query->orderBy($field, $order);

            // 分页
            $uploads = $query->paginate($request->get('limit', 20));

            // 格式化数据
            foreach ($uploads as $upload) {
                $upload->admin_name = $upload->admin ? $upload->admin->username : '未知';
                $upload->formatted_size = $upload->formatted_size;
                $upload->category_label = $upload->category_label;
                $upload->type_label = $upload->type_label;
                $upload->tags_string = $upload->tags_string;
            }

            return $this->paginate($uploads);
        }

        return view('lpadmin.upload.index');
    }

    /**
     * 上传文件
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 最大10MB
        ], [
            'file.required' => '请选择要上传的文件',
            'file.file' => '上传的必须是文件',
            'file.max' => '文件大小不能超过10MB',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $file = $request->file('file');

            // 生成文件名
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = date('YmdHis') . '_' . uniqid() . '.' . $extension;

            // 存储文件
            $path = $file->storeAs('uploads/' . date('Y/m'), $filename, 'public');
            $url = Storage::url($path);

            // 计算文件哈希
            $hash = hash_file('md5', $file->getRealPath());

            // 检查是否已存在相同文件
            $existingFile = Upload::where('hash', $hash)->first();
            if ($existingFile) {
                return $this->success([
                    'id' => $existingFile->id,
                    'url' => $existingFile->url,
                    'original_name' => $existingFile->original_name,
                ], '文件已存在，返回已有文件信息');
            }

            // 保存文件信息
            $upload = Upload::create([
                'admin_id' => $this->admin->id,
                'original_name' => $originalName,
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $extension,
                'disk' => 'public',
                'hash' => $hash,
                'metadata' => [
                    'width' => null,
                    'height' => null,
                ],
            ]);

            $this->log('create', '上传文件', ['upload_id' => $upload->id, 'filename' => $originalName]);

            return $this->success([
                'id' => $upload->id,
                'url' => $upload->url,
                'original_name' => $upload->original_name,
                'size' => $upload->size,
                'extension' => $upload->extension,
            ], '上传成功');
        } catch (\Exception $e) {
            return $this->error('上传失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除文件
     */
    public function destroy(Upload $upload): JsonResponse
    {
        try {
            // 删除物理文件
            if (Storage::disk($upload->disk)->exists($upload->path)) {
                Storage::disk($upload->disk)->delete($upload->path);
            }

            // 删除数据库记录
            $upload->delete();

            $this->log('delete', '删除文件', ['upload_id' => $upload->id, 'filename' => $upload->original_name]);

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除文件
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:' . config('database.connections.mysql.prefix') . 'uploads,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $ids = $request->ids;
            $uploads = Upload::whereIn('id', $ids)->get();

            foreach ($uploads as $upload) {
                // 删除物理文件
                if (Storage::disk($upload->disk)->exists($upload->path)) {
                    Storage::disk($upload->disk)->delete($upload->path);
                }

                // 删除数据库记录
                $upload->delete();
            }

            $this->log('delete', '批量删除文件', ['count' => count($uploads), 'ids' => $ids]);

            return $this->success(null, "成功删除 " . count($uploads) . " 个文件");
        } catch (\Exception $e) {
            return $this->error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取文件信息
     */
    public function show(Upload $upload): JsonResponse
    {
        return $this->success($upload->load('admin:id,username,nickname'));
    }

    /**
     * 文件选择器
     */
    public function selector(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Upload::query();

            // 获取配置中的文件类型定义
            $config = $this->getUploadConfig();
            $extensionsByType = [
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
                'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
                'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
                'audio' => ['mp3', 'wav', 'flac', 'aac', 'ogg', 'wma'],
                'archive' => ['zip', 'rar', '7z', 'tar', 'gz']
            ];

            // 文件类型过滤
            if ($request->filled('type')) {
                $type = $request->type;
                if (isset($extensionsByType[$type])) {
                    $extensions = $extensionsByType[$type];
                    $query->whereIn('extension', $extensions);
                }
            }

            // 搜索
            if ($request->filled('original_name')) {
                $query->where('original_name', 'like', '%' . $request->original_name . '%');
            }

            $uploads = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 20));

            // 格式化数据
            foreach ($uploads as $upload) {
                $upload->admin_name = $upload->admin ? $upload->admin->username : '未知';
                $upload->formatted_size = $upload->formatted_size;
                $upload->category_label = $upload->category_label;
                $upload->type_label = $upload->type_label;
                $upload->tags_string = $upload->tags_string;
            }

            return $this->paginate($uploads);
        }

        // 传递文件类型配置到视图
        $config = $this->getUploadConfig();
        $fileTypes = [
            'image' => '图片',
            'document' => '文档',
            'video' => '视频',
            'audio' => '音频',
            'archive' => '压缩包'
        ];

        return view('lpadmin.upload.selector', compact('fileTypes'));
    }

    /**
     * 获取上传配置（API接口）
     */
    public function config(): JsonResponse
    {
        $config = $this->getUploadConfig();

        // 将扩展名按类型分组
        $extensionsByType = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
            'audio' => ['mp3', 'wav', 'flac', 'aac', 'ogg', 'wma'],
            'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'code' => ['js', 'css', 'html', 'php', 'py', 'java', 'cpp', 'c', 'json', 'xml'],
        ];

        // 根据配置的扩展名重新分组
        $allowedExtensions = $config['allowed_extensions'];
        $configuredExtensions = [];

        foreach ($extensionsByType as $type => $extensions) {
            $configuredExtensions[$type] = array_intersect($extensions, $allowedExtensions);
            // 添加配置中有但默认分组中没有的扩展名
            foreach ($allowedExtensions as $ext) {
                if (!in_array($ext, array_merge(...array_values($extensionsByType)))) {
                    $configuredExtensions['other'][] = $ext;
                }
            }
        }

        return $this->success([
            'max_size' => $config['max_size'] * 1024, // 转换为字节
            'allowed_extensions' => $configuredExtensions,
            'mime_types' => [
                'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'],
                'document' => [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain',
                    'application/rtf'
                ],
                'video' => ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska', 'video/webm'],
                'audio' => ['audio/mpeg', 'audio/wav', 'audio/flac', 'audio/aac', 'audio/ogg', 'audio/x-ms-wma'],
                'archive' => ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip'],
            ],
            'upload_url' => route('lpadmin.upload.store'),
            'disk' => $config['disk'],
            'path' => $config['path'],
            'enable_security_check' => $config['enable_security_check'],
            'enable_duplicate_check' => $config['enable_duplicate_check'],
        ]);
    }

    /**
     * 文件上传配置管理页面
     */
    public function configPage(): View
    {
        return view('lpadmin.upload.config');
    }

    /**
     * 更新文件上传配置
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'max_size' => 'required|integer|min:1|max:102400', // 1KB - 100MB
            'disk' => 'required|string|in:public,local',
            'path' => 'required|string|max:255',
            'allowed_extensions' => 'required|array',
            'allowed_extensions.*' => 'required|string|max:10',
            'enable_security_check' => 'sometimes|boolean',
            'enable_duplicate_check' => 'sometimes|boolean',
        ], [
            'max_size.required' => '请设置最大文件大小',
            'max_size.integer' => '文件大小必须是整数',
            'max_size.min' => '文件大小不能小于1KB',
            'max_size.max' => '文件大小不能超过100MB',
            'disk.required' => '请选择存储方式',
            'disk.in' => '存储方式选择无效',
            'path.required' => '请设置存储路径',
            'path.max' => '存储路径不能超过255个字符',
            'allowed_extensions.required' => '请设置允许的文件扩展名',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            // 保存配置到数据库
            $configData = [
                'upload_max_size' => $request->max_size,
                'upload_disk' => $request->disk,
                'upload_path' => $request->path,
                'upload_allowed_extensions' => json_encode($request->allowed_extensions),
                'enable_security_check' => $request->has('enable_security_check') ? (bool)$request->enable_security_check : true,
                'enable_duplicate_check' => $request->has('enable_duplicate_check') ? (bool)$request->enable_duplicate_check : true,
            ];

            // 批量更新配置
            foreach ($configData as $name => $value) {
                Option::updateOrCreate(
                    ['name' => $name],
                    [
                        'name' => $name,
                        'value' => $value,
                        'group' => 'upload',
                        'title' => $this->getConfigTitle($name),
                        'type' => $this->getConfigType($name),
                        'description' => $this->getConfigDescription($name),
                        'sort' => $this->getConfigSort($name),
                    ]
                );
            }

            // 清除配置缓存
            Option::clearCache();

            $this->log('update', '更新文件上传配置', $request->all());

            return $this->success([], '配置更新成功');
        } catch (\Exception $e) {
            return $this->error('配置更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取配置项标题
     */
    private function getConfigTitle(string $name): string
    {
        $titles = [
            'upload_max_size' => '最大文件大小',
            'upload_disk' => '存储方式',
            'upload_path' => '存储路径',
            'upload_allowed_extensions' => '允许的文件扩展名',
            'upload_enable_security_check' => '启用安全检查',
            'upload_enable_duplicate_check' => '启用重复文件检查',
        ];

        return $titles[$name] ?? $name;
    }

    /**
     * 获取配置项类型
     */
    private function getConfigType(string $name): string
    {
        $types = [
            'upload_max_size' => Option::TYPE_NUMBER,
            'upload_disk' => Option::TYPE_SELECT,
            'upload_path' => Option::TYPE_TEXT,
            'upload_allowed_extensions' => Option::TYPE_TEXTAREA,
            'upload_enable_security_check' => Option::TYPE_SWITCH,
            'upload_enable_duplicate_check' => Option::TYPE_SWITCH,
        ];

        return $types[$name] ?? Option::TYPE_TEXT;
    }

    /**
     * 获取配置项描述
     */
    private function getConfigDescription(string $name): string
    {
        $descriptions = [
            'upload_max_size' => '单个文件最大大小限制，单位：KB',
            'upload_disk' => '文件存储位置选择',
            'upload_path' => '文件存储的相对路径',
            'upload_allowed_extensions' => '允许上传的文件扩展名列表',
            'upload_enable_security_check' => '是否启用文件安全检查',
            'upload_enable_duplicate_check' => '是否启用重复文件检查',
        ];

        return $descriptions[$name] ?? '';
    }

    /**
     * 获取配置项排序
     */
    private function getConfigSort(string $name): int
    {
        $sorts = [
            'upload_max_size' => 1,
            'upload_disk' => 2,
            'upload_path' => 3,
            'upload_allowed_extensions' => 4,
            'upload_enable_security_check' => 5,
            'upload_enable_duplicate_check' => 6,
        ];

        return $sorts[$name] ?? 99;
    }

    /**
     * 通用图片上传方法
     */
    public function uploadImage(Request $request): JsonResponse
    {
        return $this->handleUpload($request, [
            'file' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120'
        ], [
            'file.required' => '请选择要上传的图片',
            'file.image' => '文件必须是图片格式',
            'file.mimes' => '图片格式只支持 jpeg、jpg、png、gif、webp',
            'file.max' => '图片大小不能超过5MB'
        ], 'images');
    }

    /**
     * 通用文件上传处理方法
     */
    private function handleUpload(Request $request, array $rules, array $messages, string $folder = 'files'): JsonResponse
    {
        try {
            $request->validate($rules, $messages);

            $file = $request->file('file');

            // 安全检查
            $securityCheck = $this->performSecurityCheck($file);
            if (!$securityCheck['safe']) {
                return $this->error($securityCheck['message']);
            }

            $config = $this->getUploadConfig();
            $disk = $config['disk'];
            $path = $config['path'];

            // 生成文件名
            $extension = $file->getClientOriginalExtension();
            $filename = date('YmdHis') . '_' . uniqid() . '.' . $extension;

            // 计算文件哈希
            $hash = hash_file('md5', $file->getRealPath());

            // 检查是否已存在相同文件
            $existingFile = Upload::where('hash', $hash)->first();
            if ($existingFile) {
                return $this->success([
                    'id' => $existingFile->id,
                    'url' => $existingFile->url,
                    'original_name' => $existingFile->original_name,
                    'size' => $existingFile->size,
                ], '文件已存在，返回已有文件信息');
            }

            // 存储文件到指定文件夹
            $storedPath = $file->storeAs($path . '/' . $folder . '/' . date('Y/m/d'), $filename, $disk);

            // 生成访问URL
            $url = Storage::url($storedPath);

            // 获取图片尺寸信息（如果是图片）
            $metadata = $this->getFileMetadata($file);

            // 创建上传记录
            $uploadData = [
                'admin_id' => auth('lpadmin')->id(),
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $storedPath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
                'disk' => $disk,
                'url' => $url,
                'hash' => $hash,
                'metadata' => $metadata,
            ];

            // 只有当字段存在时才添加新字段
            if (Schema::hasColumn('lp_uploads', 'category')) {
                $uploadData['category'] = $request->get('category', '');
            }
            if (Schema::hasColumn('lp_uploads', 'description')) {
                $uploadData['description'] = ''; // 不再从请求中获取描述
            }

            $upload = new Upload($uploadData);

            // 自动检测分类（如果未指定且字段存在）
            if (Schema::hasColumn('lp_uploads', 'category') && empty($upload->category)) {
                $upload->category = $upload->detectCategory();
            }

            // 处理标签（如果字段存在）
            if (Schema::hasColumn('lp_uploads', 'tags') && $request->filled('tags')) {
                if (is_string($request->tags)) {
                    $upload->setTagsFromString($request->tags);
                } elseif (is_array($request->tags)) {
                    $upload->tags = $request->tags;
                }
            }

            $upload->save();

            $this->log('create', '上传文件', ['upload_id' => $upload->id, 'filename' => $upload->original_name]);

            return $this->success([
                'id' => $upload->id,
                'url' => $url,
                'original_name' => $upload->original_name,
                'size' => $upload->size,
                'extension' => $upload->extension,
                'type_label' => $upload->type_label,
            ], '文件上传成功');

        } catch (\Exception $e) {
            return $this->error('上传失败：' . $e->getMessage(),200);
        }
    }



    /**
     * 通用文件上传方法
     */
    public function uploadFile(Request $request): JsonResponse
    {
        $config = $this->getUploadConfig();
        $maxSize = $config['max_size'];

        return $this->handleUpload($request, [
            'file' => "required|file|max:{$maxSize}"
        ], [
            'file.required' => '请选择要上传的文件',
            'file.max' => "文件大小不能超过{$maxSize}KB"
        ], 'files');
    }

    /**
     * 执行文件安全检查
     */
    private function performSecurityCheck($file): array
    {
        // 检查文件扩展名
        $extension = strtolower($file->getClientOriginalExtension());
        $config = $this->getUploadConfig();
        $allowedExtensions = $config['allowed_extensions'];

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'safe' => false,
                'message' => "不允许上传 .{$extension} 格式的文件"
            ];
        }

        // 检查MIME类型
        $mimeType = $file->getMimeType();
        $dangerousMimes = [
            'application/x-executable',
            'application/x-msdownload',
            'application/x-msdos-program',
            'application/x-msi',
            'application/x-bat',
            'application/x-sh',
            'text/x-php',
            'application/x-httpd-php',
        ];

        if (in_array($mimeType, $dangerousMimes)) {
            return [
                'safe' => false,
                'message' => '检测到危险文件类型，禁止上传'
            ];
        }

        // 检查文件内容（简单的恶意代码检测）
        if ($this->containsMaliciousContent($file)) {
            return [
                'safe' => false,
                'message' => '文件包含可疑内容，禁止上传'
            ];
        }

        return ['safe' => true, 'message' => ''];
    }

    /**
     * 检查文件是否包含恶意内容
     */
    private function containsMaliciousContent($file): bool
    {
        $content = file_get_contents($file->getRealPath());

        // 检查常见的恶意代码特征
        $maliciousPatterns = [
            '/<\?php.*?eval\s*\(/i',
            '/<script.*?>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/base64_decode\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取文件元数据
     */
    private function getFileMetadata($file): array
    {
        $metadata = [
            'width' => null,
            'height' => null,
            'duration' => null,
            'pages' => null,
        ];

        $mimeType = $file->getMimeType();

        // 如果是图片，获取尺寸信息
        if (str_starts_with($mimeType, 'image/')) {
            try {
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                }
            } catch (\Exception) {
                // 忽略获取图片信息失败的情况
            }
        }

        // 如果是PDF，尝试获取页数（需要安装相关扩展）
        if ($mimeType === 'application/pdf') {
            try {
                // 这里可以集成PDF处理库来获取页数
                // $metadata['pages'] = $this->getPdfPageCount($file->getRealPath());
            } catch (\Exception) {
                // 忽略获取PDF信息失败的情况
            }
        }

        return $metadata;
    }

    /**
     * 获取文件分类列表
     */
    public function categories(): JsonResponse
    {
        try {
            // 尝试获取分类统计
            $statistics = [
                'general' => Upload::where('category', Upload::CATEGORY_GENERAL)->count(),
                'avatar' => Upload::where('category', Upload::CATEGORY_AVATAR)->count(),
                'document' => Upload::where('category', Upload::CATEGORY_DOCUMENT)->count(),
                'image' => Upload::where('category', Upload::CATEGORY_IMAGE)->count(),
                'video' => Upload::where('category', Upload::CATEGORY_VIDEO)->count(),
                'audio' => Upload::where('category', Upload::CATEGORY_AUDIO)->count(),
                'archive' => Upload::where('category', Upload::CATEGORY_ARCHIVE)->count(),
            ];
        } catch (\Exception $e) {
            // 如果 category 字段不存在，返回默认统计
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $statistics = [
                    'general' => Upload::count(),
                    'avatar' => 0,
                    'document' => 0,
                    'image' => 0,
                    'video' => 0,
                    'audio' => 0,
                    'archive' => 0,
                ];
            } else {
                throw $e;
            }
        }

        return $this->success([
            'categories' => Upload::$categoryLabels,
            'statistics' => $statistics
        ]);
    }

    /**
     * 获取文件统计信息
     */
    public function statistics(): JsonResponse
    {
        try {
            // 基础统计
            $totalFiles = Upload::count();
            $totalSize = Upload::sum('size');
            $todayFiles = Upload::whereDate('created_at', today())->count();
            $thisWeekFiles = Upload::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();

            // 格式化文件大小
            $totalSizeFormatted = $this->formatFileSize($totalSize);

            // 分类统计
            $categories = [
                '' => '自动检测',
                'general' => '通用文件',
                'avatar' => '头像图片',
                'document' => '文档资料',
                'image' => '图片素材',
                'video' => '视频文件',
                'audio' => '音频文件',
                'archive' => '压缩包'
            ];

            $categoryStats = [];
            foreach ($categories as $key => $label) {
                try {
                    if ($key === '') {
                        $count = Upload::whereNull('category')->orWhere('category', '')->count();
                    } else {
                        $count = Upload::where('category', $key)->count();
                    }
                    $categoryStats[$key] = [
                        'label' => $label,
                        'count' => $count
                    ];
                } catch (\Exception $e) {
                    $categoryStats[$key] = [
                        'label' => $label,
                        'count' => 0
                    ];
                }
            }

            return response()->json([
                'code' => 0,
                'message' => '获取统计信息成功',
                'data' => [
                    'total_files' => $totalFiles,
                    'total_size' => $totalSize,
                    'total_size_formatted' => $totalSizeFormatted,
                    'today_files' => $todayFiles,
                    'this_week_files' => $thisWeekFiles,
                    'categories' => $categoryStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 1,
                'message' => '获取统计信息失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes): string
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = 1024;
        $index = floor(log($bytes) / log($base));
        $size = round($bytes / pow($base, $index), 2);

        return $size . ' ' . $units[$index];
    }

    /**
     * 更新文件信息
     */
    public function updateFile(Request $request, Upload $upload): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'nullable|string|in:' . implode(',', array_keys(Upload::$categoryLabels)),
            'tags' => 'nullable|string',
            'description' => 'nullable|string|max:500',
        ], [
            'category.in' => '分类选择无效',
            'description.max' => '描述不能超过500个字符',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            if ($request->filled('category')) {
                $upload->category = $request->category;
            }

            if ($request->filled('description')) {
                $upload->description = $request->description;
            }

            if ($request->has('tags')) {
                if (is_string($request->tags)) {
                    $upload->setTagsFromString($request->tags);
                } else {
                    $upload->tags = $request->tags;
                }
            }

            $upload->save();

            $this->log('update', '更新文件信息', ['upload_id' => $upload->id]);

            return $this->success($upload, '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取热门标签
     */
    public function popularTags(): JsonResponse
    {
        try {
            // 获取所有标签并统计使用次数
            $uploads = Upload::whereNotNull('tags')->get();
            $tagCounts = [];

            foreach ($uploads as $upload) {
                if (is_array($upload->tags)) {
                    foreach ($upload->tags as $tag) {
                        $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                    }
                }
            }

            // 按使用次数排序，取前20个
            arsort($tagCounts);
            $popularTags = array_slice($tagCounts, 0, 20, true);

            return $this->success([
                'tags' => array_keys($popularTags),
                'counts' => $popularTags,
            ]);
        } catch (\Exception $e) {
            return $this->error('获取标签失败: ' . $e->getMessage());
        }
    }

    /**
     * 文件预览
     */
    public function preview(Upload $upload): JsonResponse|View
    {
        try {
            $previewData = [
                'id' => $upload->id,
                'original_name' => $upload->original_name,
                'url' => $upload->full_url,
                'size' => $upload->formatted_size,
                'type' => $upload->type_label,
                'mime_type' => $upload->mime_type,
                'extension' => $upload->extension,
                'metadata' => $upload->metadata,
                'created_at' => $upload->created_at->format('Y-m-d H:i:s'),
                'admin_name' => $upload->admin ? $upload->admin->username : '未知',
                'category_label' => $upload->category_label,
                'tags' => $upload->tags,
                'description' => $upload->description,
            ];

            // 根据文件类型提供不同的预览方式
            if ($upload->isImage()) {
                $previewData['preview_type'] = 'image';
                $previewData['can_preview'] = true;
            } elseif ($upload->isVideo()) {
                $previewData['preview_type'] = 'video';
                $previewData['can_preview'] = in_array($upload->extension, ['mp4', 'webm', 'ogg']);
            } elseif ($upload->isAudio()) {
                $previewData['preview_type'] = 'audio';
                $previewData['can_preview'] = in_array($upload->extension, ['mp3', 'wav', 'ogg']);
            } elseif ($upload->mime_type === 'application/pdf') {
                $previewData['preview_type'] = 'pdf';
                $previewData['can_preview'] = true;
            } elseif (in_array($upload->extension, ['txt', 'json', 'xml', 'css', 'js', 'html', 'php', 'py'])) {
                $previewData['preview_type'] = 'text';
                $previewData['can_preview'] = true;
                // 对于文本文件，读取内容（限制大小）
                if ($upload->size <= 1024 * 1024) { // 1MB以下的文本文件
                    try {
                        $content = Storage::disk($upload->disk)->get($upload->path);
                        $previewData['content'] = mb_convert_encoding($content, 'UTF-8', 'auto');
                    } catch (\Exception $e) {
                        $previewData['content'] = '无法读取文件内容';
                    }
                }
            } else {
                $previewData['preview_type'] = 'download';
                $previewData['can_preview'] = false;
            }

            return $this->success($previewData);
        } catch (\Exception $e) {
            return $this->error('预览失败: ' . $e->getMessage());
        }
    }

    /**
     * 下载文件
     */
    public function download(Upload $upload)
    {
        try {
            if (!Storage::disk($upload->disk)->exists($upload->path)) {
                abort(404, '文件不存在');
            }

            $this->log('download', '下载文件', ['upload_id' => $upload->id, 'filename' => $upload->original_name]);

            $filePath = storage_path('app/public/' . $upload->path);
            if ($upload->disk === 'public' && file_exists($filePath)) {
                return response()->download($filePath, $upload->original_name);
            }

            // 对于其他存储方式，获取文件内容并返回
            $content = Storage::disk($upload->disk)->get($upload->path);
            return response($content)
                ->header('Content-Type', $upload->mime_type)
                ->header('Content-Disposition', 'attachment; filename="' . $upload->original_name . '"');
        } catch (\Exception $e) {
            abort(500, '下载失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取文件缩略图
     */
    public function thumbnail(Upload $upload, Request $request)
    {
        if (!$upload->isImage()) {
            abort(404, '不支持的文件类型');
        }

        try {
            $width = $request->get('w', 200);
            $height = $request->get('h', 200);
            $quality = $request->get('q', 80);

            // 生成缩略图缓存路径
            $thumbnailPath = "thumbnails/{$upload->id}_{$width}x{$height}_{$quality}.jpg";

            // 检查缩略图是否已存在
            if (Storage::disk('public')->exists($thumbnailPath)) {
                $thumbnailFullPath = storage_path('app/public/' . $thumbnailPath);
                return response()->file($thumbnailFullPath);
            }

            // 生成缩略图（这里需要图片处理库，如Intervention Image）
            // 简化版本：直接返回原图
            if (Storage::disk($upload->disk)->exists($upload->path)) {
                $content = Storage::disk($upload->disk)->get($upload->path);
                return response($content)->header('Content-Type', $upload->mime_type);
            }

            abort(404, '文件不存在');
        } catch (\Exception $e) {
            abort(500, '生成缩略图失败: ' . $e->getMessage());
        }
    }
}
