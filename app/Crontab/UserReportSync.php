<?php

declare(strict_types=1);

namespace App\Crontab;

use App\Http\Backend\Dao\UserReportDao;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 每 20 分钟计算当天用户概览统计，写入 ext_user_reports。
 * 若当天记录已存在则 UPDATE，否则 INSERT。
 */
#[Crontab(name: 'user_report_sync', rule: '0 */20 * * * *', callback: 'execute', memo: '每20分钟计算当天用户概览统计')]
class UserReportSync
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function execute(): void
    {
        $reportDate = date('Y-m-d');
        $this->logger->info('UserReportSync: start, report_date=' . $reportDate);
        $result = UserReportDao::computeAndUpsert($reportDate);
        if ($result['error'] !== '') {
            $this->logger->warning('UserReportSync: ' . $result['error']);
        } else {
            $action = $result['data']['action'] ?? 'unknown';
            $this->logger->info('UserReportSync: done, action=' . $action);
        }
    }
}
