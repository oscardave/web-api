<?php

use Hyperf\Crontab\Crontab;

return [
    'enable' => true,
    // 通过配置文件定义的定时任务
    'crontab' => [
        // 秒钟域（1） 分钟域（2） 小时域（3） 日期域（4） 月份域（5） 星期域（6）
//        (new Crontab())
//            ->setName('vip')
//            ->setRule('0 */10 2 * * *') // ->setRule('')//
//            ->setCallback([App\Crontab\Vip::class, 'execute'])
//            ->setMemo('每日升级'),
//        (new Crontab())
//            ->setName('lottery_auto')
//            ->setRule('0 */10 *  * * *') // ->setRule('0 * *  * * *')//
//            ->setCallback([App\Crontab\LotteryDraw::class, 'execute'])
//            ->setMemo('公共开奖'),
//        (new Crontab())
//            ->setName('market_line')
//            ->setRule('0 */3 * * * *') // ->setRule('0 * *  * * *')//
//            ->setCallback([App\Crontab\MarketLine::class, 'execute'])
//            ->setMemo('k线行情'),

//        (new Crontab())
//            ->setName('currency_price')
//            ->setRule('0 * *  * * *') // ->setRule('0 * *  * * *')//
//            ->setCallback([App\Crontab\CurrencyPrice::class, 'execute'])
//            ->setMemo('me价格'),
//        (new Crontab())
//            ->setName('commission')
//            ->setRule('0 * * * * *') // ->setRule('')//
//            ->setCallback([App\Crontab\Commission::class, 'execute'])
//            ->setMemo('注单结算'),
//         (new Crontab())
//            ->setName('Burn')
//            ->setRule('0 0 1 * * *') ///
//            ->setCallback([App\Crontab\Burn::class, 'execute'])
//            ->setMemo('燃烧me'),
//        (new Crontab())
//            ->setName('check_vip')
//            ->setRule('0 0 0  * * *') // ->setRule('0 * *  * * *')//
//            ->setCallback([App\Crontab\CheckVip::class, 'execute'])
//            ->setMemo('vip检测'),

    ],
];