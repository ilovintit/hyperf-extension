<?php

declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractController
 * @package Iit\HyLib\Contracts
 */
abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

}
