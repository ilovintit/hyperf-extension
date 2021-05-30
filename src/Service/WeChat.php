<?php
declare(strict_types=1);

namespace Iit\HyLib\Service;

use EasyWeChat\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Logger\LoggerFactory;
use Iit\HyLib\Utils\Str;
use Psr\SimpleCache\CacheInterface;
use EasyWeChat\MiniProgram\Application as MiniProgramApplication;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;
use EasyWeChat\Payment\Application as PaymentApplication;

class WeChat
{
    const TYPE_OFFICIAL_ACCOUNT = 'officialAccount';
    const TYPE_MINI_PROGRAM = 'miniProgram';
    const TYPE_PAYMENT = 'payment';

    /**
     * @var MiniProgramApplication|OfficialAccountApplication|PaymentApplication $app
     */
    public $app;

    /**
     * WeChat constructor.
     * @param CacheInterface $cache
     * @param LoggerFactory $logger
     * @param string $type
     * @param string $name
     */
    public function __construct(CacheInterface $cache, LoggerFactory $logger, $type = self::TYPE_OFFICIAL_ACCOUNT, $name = 'default')
    {
        $this->app = Factory::$type($this->getConfig($type, $name));
        $this->app['cache'] = $cache;
        $this->app['logger'] = $logger->get('we-chat');
        $handler = new CoroutineHandler();
        $httpConfig = $this->app['config']->get('http', []);
        $httpConfig['handler'] = $stack = HandlerStack::create($handler);
        $this->app->rebind('http_client', new Client($httpConfig));
        $this->app['guzzle_handler'] = $handler;
        $this->app instanceof OfficialAccountApplication &&
        $this->app->oauth->setGuzzleOptions([
            'http_errors' => false,
            'handler' => $stack
        ]);
    }

    /**
     * @param string $type
     * @param string $name
     * @return array
     */
    protected function getConfig(string $type, string $name): array
    {
        return config('library.services.wechat.' . Str::snakeCase($type) . '.' . $name, []);
    }
}
