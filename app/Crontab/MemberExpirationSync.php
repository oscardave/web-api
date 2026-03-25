<?php

declare(strict_types=1);

namespace App\Crontab;

use App\Http\Backend\Dao\UserExtDao;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * 每 5 分钟将已过期的会员（member_expiration_time < 当前时间）降级为普通用户。
 */
#[Crontab(name: 'member_expiration_sync', rule: '0 */5 * * * *', callback: 'execute', memo: '每5分钟将过期会员降级为普通用户')]
class MemberExpirationSync
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function execute(): void
    {
        $this->logger->info('MemberExpirationSync: start');
        $result = UserExtDao::expireMembers();
        if ($result['error'] !== '') {
            $this->logger->warning('MemberExpirationSync: ' . $result['error']);
        } else {
            $affected = $result['data']['affected'] ?? 0;
            $this->logger->info('MemberExpirationSync: done, expired ' . $affected . ' members');
        }
    }
}
