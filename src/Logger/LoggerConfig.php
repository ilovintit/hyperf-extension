<?php
declare(strict_types=1);

namespace Iit\HyLib\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class LoggerConfig
 * @package Iit\HyLib\Logger
 */
class LoggerConfig
{
    /**
     * @param int $defaultStreamLevel
     * @param string $defaultStreamPath
     * @return array
     */
    public static function stream(int $defaultStreamLevel = Logger::DEBUG, string $defaultStreamPath = 'php://stdout'): array
    {
        return [
            'handler' => [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => env('LOG_STREAM_PATH', $defaultStreamPath),
                    'level' => env('LOG_STREAM_LEVEL', $defaultStreamLevel)
                ],
            ],
            'formatter' => [
                'class' => StreamCustomJsonFormatter::class,
            ],
        ];
    }
}
