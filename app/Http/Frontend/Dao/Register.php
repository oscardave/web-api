<?php
namespace App\Http\Frontend\Dao;

use Hyperf\HttpServer\Contract\RequestInterface;

class Register
{
    public static function reg(RequestInterface $request, array $data, array &$result): string
    {
        return '';
    }

    public static function isSmsOff(): bool
    {
        return false;
    }

    public static function isRegisterOff(): bool
    {
        return false;
    }

    public static function sendSMS(array $data): string
    {
        return '';
    }
}