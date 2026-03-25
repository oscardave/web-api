<?php

declare(strict_types=1);

namespace App\Crontab;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * 每 3 分钟将已过期的验证码（expired_at < 当前时间 且 status=1）标记为无效。
 */
#[Crontab(name: 'verify_codes_expire', rule: '0 */3 * * * *', callback: 'execute', memo: '每3分钟将过期验证码标记为无效')]
class VerifyCodesExpire
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function execute(): void
    {
        $this->logger->info('VerifyCodesExpire: start');
        try {
            $affected = Db::table('ext_verify_codes')
                ->whereRaw('expired_at < CURRENT_TIMESTAMP')
                ->where('status', 1)
                ->update([
                    'status' => 2,
                    'is_expired' => true,
                    'updated_at' => Db::raw('CURRENT_TIMESTAMP'),
                ]);
            $this->logger->info('VerifyCodesExpire: done, marked ' . $affected . ' expired');
        } catch (\Throwable $e) {
            $this->logger->warning('VerifyCodesExpire: ' . $e->getMessage());
        }
    }
}
