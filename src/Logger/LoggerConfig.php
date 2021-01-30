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
     * @return array
     */
    public static function stream(): array
    {
        return [
            'handler' => [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => env('LOG_STREAM_PATH', 'php://stdout'),
                    'level' => env('LOG_STREAM_LEVEL', Logger::INFO)
                ],
            ],
            'formatter' => [
                'class' => StreamCustomJsonFormatter::class,
            ],
        ];
    }
}
