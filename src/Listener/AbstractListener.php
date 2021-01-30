<?php
declare(strict_types=1);

namespace App\Extension\Contracts;

use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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

    abstract public static function getListenOrder();
}
