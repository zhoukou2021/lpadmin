<?php

namespace App\Http\Controllers\LPadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ErrorTestController extends Controller
{
    /**
     * 显示错误测试页面
     */
    public function index()
    {
        return view('lpadmin.test.error-test');
    }

    /**
     * 测试403错误
     */
    public function test403()
    {
        throw new AccessDeniedHttpException('您没有权限访问此资源');
    }

    /**
     * 测试404错误
     */
    public function test404()
    {
        throw new NotFoundHttpException('请求的资源不存在');
    }

    /**
     * 测试419错误
     */
    public function test419()
    {
        throw new TokenMismatchException('CSRF token 不匹配');
    }

    /**
     * 测试429错误
     */
    public function test429()
    {
        throw new TooManyRequestsHttpException(30, '请求过于频繁');
    }

    /**
     * 测试500错误
     */
    public function test500()
    {
        throw new \Exception('这是一个测试的服务器内部错误');
    }
}
