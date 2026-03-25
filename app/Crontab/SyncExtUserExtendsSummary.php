<?php

declare(strict_types=1);

namespace App\Crontab;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

/**
 * 每 5 分钟根据 ext_user_notes、Synapse account_data(m.direct) 重算并更新 ext_user_extends 的汇总字段，
 * 供 web-ht 用户管理「详细」中的笔记数量、超级坐席笔记、好友数量展示。
 *
 * 好友数量：来自 Synapse 的 account_data 表，account_data_type = 'm.direct' 的 content（JSON）
 *   key 的个数，即该用户通过 PUT .../account_data/m.direct 维护的直接会话/好友数量。
 * 若 ext_user_extends.user_id 存的是数字（ext_users.id），则通过 ext_users 解析出 Matrix ID 再统计。
 */
#[Crontab(name: 'sync_ext_user_extends_summary', rule: '0 */5 * * * *', callback: 'execute', memo: '每5分钟更新ext_user_extends笔记与好友汇总')]
class SyncExtUserExtendsSummary
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    /**
     * 从 Synapse account_data 表取 m.direct 的 content，好友数 = JSON 对象 key 的个数。
     * 表 account_data 与 Synapse 共用（user_id, account_data_type, content）。
     */
    private function getFriendsCountFromSynapseAccountData(string $matrixUserId): int
    {
        $content = Db::table('account_data')
            ->where('user_id', $matrixUserId)
            ->where('account_data_type', 'm.direct')
            ->value('content');
        if ($content === null || $content === '') {
            return 0;
        }
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            return 0;
        }
        return count($decoded);
    }

    /** 解析用于 ext_user_notes / account_data 统计的账号 ID（Matrix ID）。ext_user_extends.user_id 可能为 Matrix ID 或 ext_users.id 数字。 */
    private function resolveAccountId(mixed $extendUserId): ?string
    {
        if ($extendUserId === null || $extendUserId === '') {
            return null;
        }
        $s = (string) $extendUserId;
        if (str_starts_with($s, '@')) {
            return $s;
        }
        $matrixId = Db::table('ext_users')->where('id', (int) $extendUserId)->value('user_id');
        return $matrixId !== null ? (string) $matrixId : $s;
    }

    public function execute(): void
    {
        $this->logger->info('SyncExtUserExtendsSummary: start');
        $updated = 0;
        $failed = 0;
        try {
            $rows = Db::table('ext_user_extends')->select('id', 'user_id')->get();
            foreach ($rows as $row) {
                try {
                    $accountId = $this->resolveAccountId($row->user_id);
                    if ($accountId === null || $accountId === '') {
                        $failed++;
                        continue;
                    }
                    $noteCount = (int) Db::table('ext_user_notes')
                        ->where('user_id', $accountId)
                        ->where('status', 1)
                        ->where('type', 0)
                        ->count();
                    $superNoteCount = (int) Db::table('ext_user_notes')
                        ->where('user_id', $accountId)
                        ->where('status', 1)
                        ->where('type', 1)
                        ->count();
                    $friendsCount = $this->getFriendsCountFromSynapseAccountData($accountId);

                    Db::table('ext_user_extends')->where('id', $row->id)->update([
                        'current_note_count' => $noteCount,
                        'current_super_seat_note_count' => $superNoteCount,
                        'friends_count' => $friendsCount,
                        'updated_at' => Db::raw('CURRENT_TIMESTAMP'),
                    ]);
                    $updated++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->logger->warning('SyncExtUserExtendsSummary: row id=' . ($row->id ?? '') . ' ' . $e->getMessage());
                }
            }
            $this->logger->info('SyncExtUserExtendsSummary: done, updated=' . $updated . ', failed=' . $failed);
        } catch (\Throwable $e) {
            $this->logger->warning('SyncExtUserExtendsSummary: ' . $e->getMessage());
        }
    }
}
