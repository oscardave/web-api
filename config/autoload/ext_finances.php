<?php

declare(strict_types=1);

/**
 * 扩展财务相关配置（诚信保等）
 */
return [
    // 诚信保申请审核通过后，延迟多少秒再执行解冻转积分（默认 600 = 10 分钟）
    'credit_fetch_timeout' => (int) (env('CREDIT_FETCH_TIMEOUT', 600)),
];
