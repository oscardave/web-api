<?php

declare(strict_types=1);

namespace App\Crontab;

use App\Http\Backend\Dao\CircleDao;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * 每 2 分钟刷新圈子统计：成员总数、今日新增、日活人数、24h 帮办数。
 * 会员数量暂写 0，待业务定义后再对接。
 */
#[Crontab(name: 'circle_stats_sync', rule: '0 */2 * * * *', callback: 'execute', memo: '每2分钟同步圈子成员与帮办统计')]
class CircleStatsSync
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function execute(): void
    {
        $this->logger->info('CircleStatsSync: start');
        $result = CircleDao::refreshAllCirclesStats();
        if ($result['error'] !== '') {
            $this->logger->warning('CircleStatsSync: ' . $result['error']);
        } else {
            $processed = $result['data']['processed'] ?? 0;
            $this->logger->info('CircleStatsSync: done, processed ' . $processed . ' circles');
        }
    }
}
