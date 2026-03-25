<?php
declare(strict_types=1);

namespace App\Command;

use App\Caches\BaseCache;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class InitializeCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var string
     */
    protected ?string $name = 'initialize:command';

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('initialize:command');
    }

    /**
     * @return void
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->line('初始化系统缓存系统 -- 此命令必须在系统运行前执行!', 'info');
        BaseCache::initialize();
    }
}
