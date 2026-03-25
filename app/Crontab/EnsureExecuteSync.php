<?php

declare(strict_types=1);

namespace App\Crontab;

use App\Http\Backend\Dao\UserEnsureDao;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 每 5 分钟扫描诚信保「已审核待执行」记录，对审核通过时间超过 CREDIT_FETCH_TIMEOUT 秒的执行解冻转积分并改为已完成。
 */
#[Crontab(name: 'ensure_execute_sync', rule: '0 */5 * * * *', callback: 'execute', memo: '每5分钟执行待执行的诚信保申请（解冻转积分）')]
class EnsureExecuteSync
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function execute(): void
    {
        $this->logger->info('EnsureExecuteSync: start');
        $result = UserEnsureDao::executePendingEnsures();
        if ($result['error'] !== '') {
            $this->logger->warning('EnsureExecuteSync: ' . $result['error']);
        } else {
            $processed = $result['data']['processed'] ?? 0;
            $this->logger->info('EnsureExecuteSync: done, processed ' . $processed . ' ensures');
        }
    }
}
