<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * 手机号 E.164 归一化与校验（多国家支持）
 */
class PhoneHelper
{
    /** E.164 总长度范围（国家码 + 国内号） */
    private const E164_MIN_LEN = 10;
    private const E164_MAX_LEN = 15;

    /**
     * 将手机号归一化为 E.164 数字串（无 + 号）
     * 支持：仅中国 11 位、带国家码的 E.164、以及 country_code + 本地号
     *
     * @param string $phone 手机号（可为本地号或已含国家码，如 13800138000、8613800138000、+86 138 0013 8000）
     * @param string|null $countryCode 国家码（如 "86","1","44"），若传入则 phone 视为本地号
     * @return string 归一化后的 E.164 数字串，失败返回空字符串
     */
    public static function normalizeToE164(string $phone, ?string $countryCode = null): string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        $phone = ltrim($phone, '+');
        if ($phone === '') {
            return '';
        }
        if (!preg_match('/^\d+$/', $phone)) {
            return '';
        }

        if ($countryCode !== null && $countryCode !== '') {
            $countryCode = preg_replace('/\D/', '', $countryCode);
            if ($countryCode === '') {
                return '';
            }
            $combined = $countryCode . $phone;
            return self::isValidE164Length($combined) ? $combined : '';
        }

        // 已带国家码或纯中国 11 位
        if (preg_match('/^86\d{11}$/', $phone)) {
            return $phone;
        }
        if (preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return '86' . $phone;
        }
        if (self::isValidE164Length($phone)) {
            return $phone;
        }
        return '';
    }

    /**
     * 是否为中国大陆 11 位手机号（不含国家码）
     */
    public static function isChineseLocalNumber(string $phone): bool
    {
        return (bool) preg_match('/^1[3-9]\d{9}$/', preg_replace('/\D/', '', $phone));
    }

    /**
     * 获取用于查询 bound_phone 的可能值（兼容历史存的 86+11 与 11 位）
     * @param string $phone 原始输入
     * @param string|null $countryCode 可选国家码
     * @return array 去重后的字符串数组，用于 WHERE bound_phone IN (...)
     */
    public static function boundPhoneQueryValues(string $phone, ?string $countryCode = null): array
    {
        $e164 = self::normalizeToE164($phone, $countryCode);
        if ($e164 === '') {
            return [];
        }
        $values = [$e164];
        if (preg_match('/^86(\d{11})$/', $e164, $m)) {
            $values[] = $m[1];
        }
        return array_values(array_unique($values));
    }

    /**
     * E.164 数字串长度是否合法
     */
    private static function isValidE164Length(string $digits): bool
    {
        $len = strlen($digits);
        return $len >= self::E164_MIN_LEN && $len <= self::E164_MAX_LEN;
    }
}

