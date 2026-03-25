<?php

declare(strict_types=1);

namespace App\Crontab;

use App\Http\Backend\Dao\UserAccountDao;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

/**
 * 每 10 分钟补齐 ext_user_accounts：将 ext_users 中存在但 ext_user_accounts 中不存在的用户插入账户记录，默认 0 值。
 */
#[Crontab(name: 'user_account_sync', rule: '0 */10 * * * *', callback: 'execute', memo: '每10分钟补齐缺失的用户账户记录')]
class UserAccountSync
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function execute(): void
    {
        $this->logger->info('UserAccountSync: start');
        $result = UserAccountDao::syncMissingAccounts();
        if ($result['error'] !== '') {
            $this->logger->warning('UserAccountSync: ' . $result['error']);
        } else {
            $inserted = $result['data']['inserted'] ?? 0;
            $this->logger->info('UserAccountSync: done, inserted ' . $inserted . ' accounts');
        }
    }
}
