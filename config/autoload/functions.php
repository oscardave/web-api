<?php
declare(strict_types=1);

/**
 * cpu 核心数量
 *
 * @return int
 */
function getProcessorCoreNumber() {
    if (PHP_OS_FAMILY == 'Windows') {
        $cores = shell_exec('echo %NUMBER_OF_PROCESSORS%');
    } else {
        $cores = shell_exec('nproc');
    }

    return (int) $cores;
}
