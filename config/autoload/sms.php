<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

return [
    // 伪模式：true 时不调用真实短信 API；false 时调用 GoTone 真实接口
    'mock' => env('SMS_MOCK', false),
    // GoTone SMS API 配置
    'api_url' => env('SMS_API_URL', 'https://app.gotonesms.net/api/v3/sms/send'),
    'api_token' => env('SMS_API_TOKEN', '69533|rOfoWXpR6yGBAAmlCCpq6Whc1NdbDKuWSmJY4AUI30120516'),
    'sender_id' => env('SMS_SENDER_ID', 'SignOTP'),
];

