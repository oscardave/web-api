<?php

declare(strict_types=1);
/**
 * 邮件配置（阿里云邮件推送 SMTP）
 */

return [
    // 伪模式：true 时不调用真实 SMTP，便于开发联调
    'mock' => env('MAIL_MOCK', false),
    // SMTP 配置（阿里云 Direct Mail）
    'host' => env('MAIL_HOST', 'smtpdm.aliyun.com'),
    'port' => (int) env('MAIL_PORT', 465),
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'), // ssl 或 tls
    'username' => env('MAIL_USERNAME', 'shequan@shequanmail.cc'),
    'password' => env('MAIL_PASSWORD', 'Ce8WiHDnsr12'),
    'from_address' => env('MAIL_FROM_ADDRESS', 'shequan@shequanmail.cc'),
    'from_name' => env('MAIL_FROM_NAME', '验证码'),
];
