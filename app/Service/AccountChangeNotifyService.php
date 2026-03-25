<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Redis\Redis;
use Hyperf\Contract\ConfigInterface;

/**
 * 账变事件通知：向 Redis 发布消息，供 WebSocket 订阅端推送给对应用户。
 * 不启事务，在业务方 commit 后调用。
 */
class AccountChangeNotifyService
{
    public const CHANNEL_KEY = 'account_change_channel';

    public function __construct(
        protected Redis $redis,
        protected ConfigInterface $config
    ) {
    }

    /**
     * 发布账变事件，通知指定用户（ext_users.id）刷新积分/诚信保余额。
     *
     * @param int $extUserId ext_users.id，与 ext_user_accounts.user_id 一致
     * @param array $payload 可选附加数据，如 change_type、amount 等，便于前端区分
     */
    public function notify(int $extUserId, array $payload = []): void
    {
        $channel = $this->config->get('websocket.' . self::CHANNEL_KEY, 'account_change');
        $message = json_encode(array_merge(
            ['user_id' => $extUserId, 'ts' => time()],
            $payload
        ), JSON_UNESCAPED_UNICODE);
        $this->redis->publish($channel, $message);
    }
}
