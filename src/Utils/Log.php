<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package Iit\HyLib\Utils
 * @method static emergency($message, array $context = [])
 * @method static alert($message, array $context = [])
 * @method static critical($message, array $context = [])
 * @method static error($message, array $context = [])
 * @method static warning($message, array $context = [])
 * @method static notice($message, array $context = [])
 * @method static info($message, array $context = [])
 * @method static debug($message, array $context = [])
 */
class Log
{
    /**
     * @return string
     */
    public static function id(): string
    {
        return Context::getOrSet('logId', function () {
            $request = Context::get(ServerRequestInterface::class);
            if (empty($request)) {
                return H::uuid();
            }
            if (!method_exists($request, 'getHeaderLine')) {
                return H::uuid();
            }
            if (empty($request->getHeaderLine('X-Request-Id'))) {
                return H::uuid();
            }
            return $request->getHeaderLine('X-Request-Id');
        });
    }

    /**
     * @param string $handler
     * @return LoggerInterface
     */

    public static function driver(string $handler = 'default'): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get(config('app_env'), $handler);
    }

    /**
     * @param $name
     * @param $arguments
     */

    public static function __callStatic($name, $arguments)
    {
        self::driver()->$name(...$arguments);
    }
}
