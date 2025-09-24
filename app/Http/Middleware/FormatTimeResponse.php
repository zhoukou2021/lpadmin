<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * 全局时间格式化中间件
 */
class FormatTimeResponse
{
    /**
     * 需要格式化的时间字段
     */
    protected array $timeFields;

    /**
     * 时间格式
     */
    protected string $timeFormat;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->timeFields = config('time-format.fields', []);
        $this->timeFormat = config('time-format.format', 'Y-m-d H:i:s');
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // 检查是否启用时间格式化
        if (!config('time-format.enabled', true)) {
            return $response;
        }

        // 只处理JSON响应
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            if (is_array($data)) {
                $data = $this->formatTimeFields($data);
                $response->setData($data);
            }
        }

        return $response;
    }

    /**
     * 递归格式化时间字段
     */
    protected function formatTimeFields($data): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->formatTimeFields($value);
                } elseif (in_array($key, $this->timeFields) && $this->isTimeValue($value)) {
                    $data[$key] = $this->formatTime($value);
                }
            }
        }

        return $data;
    }

    /**
     * 判断是否为时间值
     */
    protected function isTimeValue($value): bool
    {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return false;
        }

        // 检查是否为有效的时间格式
        try {
            Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 格式化时间
     */
    protected function formatTime($value): string
    {
        try {
            return Carbon::parse($value)->format($this->timeFormat);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
