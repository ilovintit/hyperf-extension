<?php

namespace Iit\HyLib;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Server\Request\JsonParser;
use Iit\HyLib\Auth\Contract\HttpAuthContract;
use Iit\HyLib\Auth\HttpAuthManage;
use Iit\HyLib\Exceptions\Handler\AppExceptionHandler;
use Iit\HyLib\Filesystem\OssAdapterFactory;
use Iit\HyLib\Listener\DbQueryListener;
use Iit\HyLib\Listener\ValidatorFactoryResolvedListener;
use Iit\HyLib\Logger\FrameworkLoggerFactory;
use Iit\HyLib\Middleware\CoreMiddleware;
use Hyperf\HttpServer\CoreMiddleware as HyCoreMiddleware;
use Iit\HyLib\Parser\RequestJsonParser;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor as HyModelUpdateVisitor;
use Iit\HyLib\Model\ModelUpdateVisitor;

/**
 * Class ConfigProvider
 * @package Iit\HyLib
 */
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                JsonParser::class => RequestJsonParser::class,
                HyCoreMiddleware::class => CoreMiddleware::class,
                StdoutLoggerInterface::class => FrameworkLoggerFactory::class,
                HttpAuthContract::class => HttpAuthManage::class,
                HyModelUpdateVisitor::class => ModelUpdateVisitor::class
            ],
            'exceptions' => [
                'handler' => [
                    'http' => [
                        AppExceptionHandler::class,
                    ]
                ]
            ],
            'listeners' => [
                DbQueryListener::class,
                ValidatorFactoryResolvedListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'file' => [
                'storage' => [
                    'oss_ext' => [
                        'driver' => OssAdapterFactory::class,
                        'access_id' => env('OSS_ACCESS_ID'),
                        'access_secret' => env('OSS_ACCESS_SECRET'),
                        'bucket' => env('OSS_BUCKET'),
                        'endpoint' => env('OSS_ENDPOINT'),
                        'cname' => env('OSS_CNAME'),
                    ]
                ]
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for Hyperf Extension.',
                    'source' => __DIR__ . '/../publish/library.php',
                    'destination' => BASE_PATH . '/config/autoload/library.php',
                ],
            ],
        ];
    }
}
