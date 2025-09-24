<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // 检查是否是后台管理系统的请求
        if ($this->isLPadminRequest($request)) {
            return $this->renderLPadminException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * 检查是否是后台管理系统的请求
     */
    protected function isLPadminRequest(Request $request): bool
    {
        $routePrefix = lpadmin_route_prefix();
        return $request->is($routePrefix) || $request->is($routePrefix . '/*');
    }

    /**
     * 渲染后台管理系统的异常页面
     */
    protected function renderLPadminException(Request $request, Throwable $exception)
    {
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getExceptionMessage($exception, $statusCode);

        // 根据状态码选择对应的视图
        $viewName = $this->getLPadminErrorView($statusCode);

        if (view()->exists($viewName)) {
            return response()->view($viewName, [
                'code' => $statusCode,
                'message' => $message,
                'title' => $this->getErrorTitle($statusCode),
                'exception' => config('app.debug') ? $exception : null,
            ], $statusCode);
        }

        // 如果没有对应的视图，使用通用错误页面
        return response()->view('lpadmin.errors.error', [
            'code' => $statusCode,
            'message' => $message,
            'title' => $this->getErrorTitle($statusCode),
            'exception' => config('app.debug') ? $exception : null,
        ], $statusCode);
    }

    /**
     * 获取异常的HTTP状态码
     */
    protected function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof NotFoundHttpException) {
            return 404;
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return 403;
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return 429;
        }

        if ($exception instanceof TokenMismatchException) {
            return 419;
        }

        return 500;
    }

    /**
     * 获取异常消息
     */
    protected function getExceptionMessage(Throwable $exception, int $statusCode): string
    {
        // 如果是调试模式，显示详细错误信息
        if (config('app.debug') && $exception->getMessage()) {
            return $exception->getMessage();
        }

        // 根据状态码返回用户友好的错误消息
        return match($statusCode) {
            403 => '抱歉，您无权访问该页面',
            404 => '抱歉，您访问的页面不存在或已被删除',
            419 => '页面已过期，请刷新页面后重试',
            429 => '请求过于频繁，请稍后再试',
            500 => '抱歉，服务器遇到了一个错误，请稍后再试',
            default => '系统遇到了一个错误，请稍后再试',
        };
    }

    /**
     * 获取后台错误页面视图名称
     */
    protected function getLPadminErrorView(int $statusCode): string
    {
        return "lpadmin.errors.{$statusCode}";
    }

    /**
     * 获取错误标题
     */
    protected function getErrorTitle(int $statusCode): string
    {
        return match($statusCode) {
            403 => '访问被拒绝',
            404 => '页面未找到',
            419 => '页面已过期',
            429 => '请求过于频繁',
            500 => '服务器内部错误',
            default => '系统错误',
        };
    }
}
