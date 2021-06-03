<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class H
 * @package Iit\HyLib\Utils
 */
class H
{
    /**
     * @return string
     */
    public static function uuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * 获取指定对象
     * @param $id
     * @return mixed
     */
    public static function app($id)
    {
        return ApplicationContext::getContainer()->get($id);
    }

    /**
     * 获取请求信息对象
     * @return ServerRequestInterface|null
     */
    public static function request(): ?ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }

    /**
     * 生成雪花ID
     * @return int
     */
    public static function snowFlakeId(): int
    {
        return ApplicationContext::getContainer()->get(IdGeneratorInterface::class)->generate();
    }

    /**
     * 获取客户端真实ip
     * @return string|null
     */
    public static function clientRealIp(): ?string
    {
        $headerKeys = [
            'ali-cdn-real-ip',
            'x-real-up',
            'x-original-forwarded-for',
            'x-forwarded-for'
        ];
        foreach ($headerKeys as $headerKey) {
            $headerValue = self::request()->getHeaderLine($headerKey);
            if (!empty($headerValue)) {
                return $headerValue;
            }
        }
        $serverParams = self::request()->getServerParams();
        return $serverParams['remote_addr'] ?? null;
    }
}
