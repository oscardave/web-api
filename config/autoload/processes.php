<?php
declare(strict_types=1);

use Hyperf\Crontab\Process\CrontabDispatcherProcess;
use Hyperf\AsyncQueue\Process\ConsumerProcess;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    CrontabDispatcherProcess::class,
    ConsumerProcess::class,
];