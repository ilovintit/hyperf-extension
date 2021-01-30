<?php
declare(strict_types=1);

namespace App\Extension;

use App\Extension\Auth\Contract\Guard;
use App\Extension\Auth\Contract\HttpAuthContract;
use App\Extension\Services\SessionManager;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class Util
{
    /**
     * 获取客户端真实ip
     * @return string|null
     */
    public static function realIp()
    {
        $headerKeys = ['ali-cdn-real-ip', 'x-real-up', 'x-original-forwarded-for', 'x-forwarded-for'];
        foreach ($headerKeys as $headerKey) {
            $headerValue = self::request()->getHeaderLine($headerKey);
            logs()->info('get-real-ip-header-key', ['headerKey' => $headerKey, 'headerValue' => $headerValue]);
            if (!empty($headerValue)) {
                return $headerValue;
            }
        }
        $serverParams = self::request()->getServerParams();
        return isset($serverParams['remote_addr']) ? $serverParams['remote_addr'] : null;
    }

    /**
     * 获取请求信息对象
     * @return ServerRequestInterface|null
     */
    public static function request()
    {
        return Context::get(ServerRequestInterface::class);
    }

    /**
     * 微信小程序session管理器
     * @return SessionManager
     */
    public static function weChatAppletsSessionManager()
    {
        return ApplicationContext::getContainer()->get(SessionManager::class);
    }

    /**
     * 登录验证管理
     * @param null $guard
     * @return Guard|HttpAuthContract
     */
    public static function auth($guard = null)
    {
        $auth = ApplicationContext::getContainer()->get(HttpAuthContract::class);
        if (is_null($guard)) {
            return $auth;
        }
        return $auth->guard($guard);
    }

    /**
     * 异步队列管理
     * @param string $driver
     * @return DriverInterface
     */
    public static function queue($driver = 'default')
    {
        return ApplicationContext::getContainer()->get(DriverFactory::class)->get($driver);
    }

}
