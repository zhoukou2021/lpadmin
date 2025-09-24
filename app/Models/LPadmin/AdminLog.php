<?php

namespace App\Models\LPadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 管理员操作日志模型
 *
 * @property int $id
 * @property int $admin_id 管理员ID
 * @property string $admin_username 管理员用户名
 * @property string $action 操作类型
 * @property string $module 模块名称
 * @property string $route_name 路由名称
 * @property string $method 请求方法
 * @property string $url 请求URL
 * @property string $ip IP地址
 * @property string $user_agent 用户代理
 * @property string $request_data 请求数据
 * @property int $response_code 响应状态码
 * @property string $created_at 创建时间
 */
class AdminLog extends Model
{
    use HasFactory;

    /**
     * 数据表名称
     */
    protected $table = 'admin_logs';

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'admin_id',
        'admin_username',
        'action',
        'module',
        'route_name',
        'method',
        'url',
        'ip',
        'user_agent',
        'request_data',
        'response_code',
    ];

    /**
     * 属性类型转换
     */
    protected $casts = [
        'admin_id' => 'integer',
        'response_code' => 'integer',
    ];

    /**
     * 不需要更新时间戳
     */
    public $timestamps = false;

    /**
     * 操作类型常量
     */
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_OTHER = 'other';

    /**
     * 操作类型标签
     */
    public static $actionLabels = [
        self::ACTION_LOGIN => '登录',
        self::ACTION_LOGOUT => '登出',
        self::ACTION_CREATE => '创建',
        self::ACTION_UPDATE => '更新',
        self::ACTION_DELETE => '删除',
        self::ACTION_VIEW => '查看',
        self::ACTION_OTHER => '其他',
    ];

    /**
     * 获取操作类型标签
     */
    public function getActionLabelAttribute(): string
    {
        return self::$actionLabels[$this->action] ?? '未知';
    }

    /**
     * 获取请求数据（JSON解码）
     */
    public function getRequestDataArrayAttribute(): array
    {
        return json_decode($this->request_data, true) ?: [];
    }

    /**
     * 获取响应状态标签
     */
    public function getResponseStatusAttribute(): string
    {
        if ($this->response_code >= 200 && $this->response_code < 300) {
            return '成功';
        } elseif ($this->response_code >= 400 && $this->response_code < 500) {
            return '客户端错误';
        } elseif ($this->response_code >= 500) {
            return '服务器错误';
        } else {
            return '未知';
        }
    }

    /**
     * 关联管理员
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * 查询指定操作类型的日志
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 查询指定模块的日志
     */
    public function scopeOfModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * 查询指定管理员的日志
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * 查询指定IP的日志
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip', $ip);
    }

    /**
     * 查询成功的操作
     */
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('response_code', [200, 299]);
    }

    /**
     * 查询失败的操作
     */
    public function scopeFailed($query)
    {
        return $query->where('response_code', '>=', 400);
    }

    /**
     * 查询指定时间范围的日志
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 查询今天的日志
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * 查询最近的日志
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
