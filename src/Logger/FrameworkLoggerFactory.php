<?php
declare(strict_types=1);

namespace Iit\HyLib\Logger;

use Iit\HyLib\Utils\Log;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FrameworkLoggerFactory
 * @package Iit\HyLib\Logger
 */
class FrameworkLoggerFactory
{
    /**
     * @param ContainerInterface $container
     * @return LoggerInterface
     */
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        return Log::driver();
    }
}
