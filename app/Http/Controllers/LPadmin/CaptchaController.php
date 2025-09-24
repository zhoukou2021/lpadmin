<?php

namespace App\Http\Controllers\LPadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    /**
     * 生成验证码图片
     */
    public function generate(): Response
    {
        // 生成随机验证码
        $code = $this->generateCode();

        // 存储到session
        Session::put('lpadmin_captcha', strtolower($code));

        // 直接返回SVG验证码（更兼容）
        $svg = $this->createSvgImage($code);
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * 验证验证码
     */
    public function verify(string $code): bool
    {
        $sessionCode = Session::get('lpadmin_captcha');

        if (!$sessionCode) {
            return false;
        }

        return strtolower($code) === strtolower($sessionCode);
    }

    /**
     * 生成验证码字符串
     */
    private function generateCode(int $length = 4): string
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * 创建验证码图片
     */
    private function createImage(string $code): string
    {
        $width = 120;
        $height = 40;

        // 创建画布
        $image = imagecreate($width, $height);

        // 定义颜色
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 50, 50, 50);
        $lineColor = imagecolorallocate($image, 200, 200, 200);

        // 填充背景
        imagefill($image, 0, 0, $bgColor);

        // 添加干扰线
        for ($i = 0; $i < 5; $i++) {
            imageline($image,
                rand(0, $width), rand(0, $height),
                rand(0, $width), rand(0, $height),
                $lineColor
            );
        }

        // 添加干扰点
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $lineColor);
        }

        // 写入验证码文字
        $fontSize = 16;
        $fontAngle = 0;

        for ($i = 0; $i < strlen($code); $i++) {
            $x = 20 + $i * 20 + rand(-5, 5);
            $y = 25 + rand(-3, 3);
            $angle = rand(-15, 15);

            imagestring($image, $fontSize, $x, $y, $code[$i], $textColor);
        }

        // 输出图片
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();

        // 释放内存
        imagedestroy($image);

        return $imageData;
    }

    /**
     * 创建SVG验证码图片（当GD扩展不可用时）
     */
    private function createSvgImage(string $code): string
    {
        $width = 120;
        $height = 40;

        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="#f0f0f0"/>';

        // 添加干扰线
        for ($i = 0; $i < 5; $i++) {
            $x1 = rand(0, $width);
            $y1 = rand(0, $height);
            $x2 = rand(0, $width);
            $y2 = rand(0, $height);
            $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="#ccc" stroke-width="1"/>';
        }

        // 添加验证码文字
        for ($i = 0; $i < strlen($code); $i++) {
            $x = 20 + $i * 20 + rand(-5, 5);
            $y = 25 + rand(-3, 3);
            $rotate = rand(-15, 15);
            $svg .= '<text x="' . $x . '" y="' . $y . '" font-family="Arial" font-size="16" fill="#333" transform="rotate(' . $rotate . ' ' . $x . ' ' . $y . ')">' . $code[$i] . '</text>';
        }

        $svg .= '</svg>';

        return $svg;
    }
}
