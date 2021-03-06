<?php
declare(strict_types=1);

namespace Iit\HyLib\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractListener
 * @package Iit\HyLib\Contracts
 * @Listener()
 */
abstract class AbstractListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * AbstractListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return int
     */
    abstract public static function getListenOrder(): int;
}
