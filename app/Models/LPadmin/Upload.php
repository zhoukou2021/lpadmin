<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 上传文件模型
 *
 * @property int $id
 * @property int $admin_id 上传者ID
 * @property string $original_name 原始文件名
 * @property string $filename 存储文件名
 * @property string $path 文件路径
 * @property string $url 访问URL
 * @property string $mime_type MIME类型
 * @property int $size 文件大小(字节)
 * @property string $extension 文件扩展名
 * @property string $disk 存储磁盘
 * @property string $hash 文件哈希值
 * @property array $metadata 元数据
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Upload extends Model
{
    use HasFactory;

    /**
     * 数据表名称
     */
    protected $table = 'uploads';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'admin_id',
        'original_name',
        'filename',
        'path',
        'url',
        'mime_type',
        'size',
        'extension',
        'disk',
        'hash',
        'metadata',
        'category',
        'tags',
        'description',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'admin_id' => 'integer',
        'size' => 'integer',
        'metadata' => 'array',
        'tags' => 'array',
    ];

    /**
     * 文件分类常量
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_AVATAR = 'avatar';
    const CATEGORY_DOCUMENT = 'document';
    const CATEGORY_IMAGE = 'image';
    const CATEGORY_VIDEO = 'video';
    const CATEGORY_AUDIO = 'audio';
    const CATEGORY_ARCHIVE = 'archive';

    /**
     * 分类标签
     */
    public static $categoryLabels = [
        self::CATEGORY_GENERAL => '通用文件',
        self::CATEGORY_AVATAR => '头像图片',
        self::CATEGORY_DOCUMENT => '文档资料',
        self::CATEGORY_IMAGE => '图片素材',
        self::CATEGORY_VIDEO => '视频文件',
        self::CATEGORY_AUDIO => '音频文件',
        self::CATEGORY_ARCHIVE => '压缩包',
    ];

    /**
     * 关联上传者
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * 获取格式化的文件大小
     */
    public function getFormattedSizeAttribute(): string
    {
        return $this->formatBytes($this->size);
    }

    /**
     * 获取完整URL
     */
    public function getFullUrlAttribute(): string
    {
        if (filter_var($this->url, FILTER_VALIDATE_URL)) {
            return $this->url;
        }

        return asset($this->url);
    }

    /**
     * 检查是否为图片
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * 检查是否为视频
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * 检查是否为音频
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * 检查是否为文档
     */
    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ];

        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * 获取文件类型图标
     */
    public function getTypeIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'layui-icon-picture';
        } elseif ($this->isVideo()) {
            return 'layui-icon-video';
        } elseif ($this->isAudio()) {
            return 'layui-icon-music';
        } elseif ($this->isDocument()) {
            return 'layui-icon-file';
        } else {
            return 'layui-icon-file-b';
        }
    }

    /**
     * 获取文件类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->isImage()) {
            return '图片';
        } elseif ($this->isVideo()) {
            return '视频';
        } elseif ($this->isAudio()) {
            return '音频';
        } elseif ($this->isDocument()) {
            return '文档';
        } else {
            return '其他';
        }
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 查询指定类型的文件
     */
    public function scopeOfType($query, string $type)
    {
        switch ($type) {
            case 'image':
                return $query->where('mime_type', 'like', 'image/%');
            case 'video':
                return $query->where('mime_type', 'like', 'video/%');
            case 'audio':
                return $query->where('mime_type', 'like', 'audio/%');
            case 'document':
                return $query->whereIn('mime_type', [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                ]);
            default:
                return $query;
        }
    }

    /**
     * 查询指定上传者的文件
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * 查询指定分类的文件
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 获取分类标签
     */
    public function getCategoryLabelAttribute(): string
    {
        // 检查 category 字段是否存在
        if (!isset($this->attributes['category'])) {
            return '通用';
        }
        return self::$categoryLabels[$this->category] ?? '未分类';
    }

    /**
     * 自动检测文件分类
     */
    public function detectCategory(): string
    {
        if ($this->isImage()) {
            return self::CATEGORY_IMAGE;
        } elseif ($this->isVideo()) {
            return self::CATEGORY_VIDEO;
        } elseif ($this->isAudio()) {
            return self::CATEGORY_AUDIO;
        } elseif ($this->isDocument()) {
            return self::CATEGORY_DOCUMENT;
        } elseif (in_array($this->extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return self::CATEGORY_ARCHIVE;
        } else {
            return self::CATEGORY_GENERAL;
        }
    }

    /**
     * 获取标签字符串
     */
    public function getTagsStringAttribute(): string
    {
        // 检查 tags 字段是否存在
        if (!isset($this->attributes['tags'])) {
            return '';
        }

        $tags = $this->tags;
        if (is_string($tags)) {
            $tags = json_decode($tags, true);
        }

        return is_array($tags) ? implode(', ', $tags) : '';
    }

    /**
     * 设置标签
     */
    public function setTagsFromString(string $tagsString): void
    {
        $tags = array_filter(array_map('trim', explode(',', $tagsString)));
        $this->tags = $tags;
    }

    /**
     * 检查是否有指定标签
     */
    public function hasTag(string $tag): bool
    {
        return is_array($this->tags) && in_array($tag, $this->tags);
    }

    /**
     * 添加标签
     */
    public function addTag(string $tag): void
    {
        $tags = is_array($this->tags) ? $this->tags : [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
        }
    }

    /**
     * 移除标签
     */
    public function removeTag(string $tag): void
    {
        if (is_array($this->tags)) {
            $this->tags = array_values(array_filter($this->tags, function($t) use ($tag) {
                return $t !== $tag;
            }));
        }
    }
}
