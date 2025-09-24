<?php

namespace App\Http\Controllers\LPadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * LPadmin基础控制器
 *
 * 提供LPadmin模块的基础功能和通用方法
 */
class BaseController extends Controller
{
    /**
     * 当前登录的管理员
     */
    protected $admin;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->middleware('lpadmin.auth');
        $this->middleware(function ($request, $next) {
            $this->admin = auth('lpadmin')->user();
            return $next($request);
        });
    }

    /**
     * 返回成功的JSON响应
     *
     * @param mixed $data 数据
     * @param string $message 消息
     * @param int $code 状态码
     * @return JsonResponse
     */
    protected function success($data = null, string $message = '操作成功', int $code = 200): JsonResponse
    {
        // 清理数据中的UTF-8字符
        $cleanData = $this->cleanDataForJson($data);

        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => $cleanData,
            'timestamp' => time(),
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回失败的JSON响应
     *
     * @param string $message 错误消息
     * @param int $code 错误码
     * @param mixed $data 数据
     * @return JsonResponse
     */
    protected function error(string $message = '操作失败', int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'code' => 1,
            'message' => $this->cleanUtf8($message),
            'data' => $this->cleanDataForJson($data),
            'timestamp' => time(),
        ], $code, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回分页数据的JSON响应
     *
     * @param mixed $data 分页数据
     * @param string $message 消息
     * @return JsonResponse
     */
    protected function paginate($data, string $message = '获取成功'): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => $data->items(),
            'count' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
            'timestamp' => time(),
        ]);
    }

    /**
     * 返回视图
     *
     * @param string $view 视图名称
     * @param array $data 数据
     * @return View
     */
    protected function view(string $view, array $data = []): View
    {
        // 添加通用数据
        $data['admin'] = $this->admin;
        $data['system'] = config('lpadmin.system');

        return view("lpadmin.{$view}", $data);
    }

    /**
     * 记录操作日志
     *
     * @param string $action 操作
     * @param string $description 描述
     * @param array $data 数据
     * @return void
     */
    protected function log(string $action, string $description = '', array $data = []): void
    {
        // TODO: 实现日志记录功能
    }

    /**
     * 清理UTF-8字符，确保JSON编码正常
     */
    protected function cleanUtf8(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        // 移除或替换无效的UTF-8字符
        $clean = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // 移除控制字符（除了换行符、回车符、制表符）
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean);

        return $clean ?: '';
    }

    /**
     * 递归清理数据中的UTF-8字符
     */
    protected function cleanDataForJson($data)
    {
        if (is_string($data)) {
            return $this->cleanUtf8($data);
        }

        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->cleanUtf8($key) : $key;
                $cleaned[$cleanKey] = $this->cleanDataForJson($value);
            }
            return $cleaned;
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $this->cleanDataForJson($data->toArray());
            }

            $cleaned = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                $cleanKey = is_string($key) ? $this->cleanUtf8($key) : $key;
                $cleaned->$cleanKey = $this->cleanDataForJson($value);
            }
            return $cleaned;
        }

        return $data;
    }
}
