<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Request
 * @package Iit\HyLib\Utils
 */
class Request
{
    /**
     * @return ServerRequestInterface|null
     */
    public static function now(): ?ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }

    /**
     * @return string|null
     */
    public static function realIp(): ?string
    {
        $headerKeys = ['ali-cdn-real-ip', 'x-real-up', 'x-original-forwarded-for', 'x-forwarded-for'];
        foreach ($headerKeys as $headerKey) {
            $headerValue = self::now()->getHeaderLine($headerKey);
            if (!empty($headerValue)) {
                return $headerValue;
            }
        }
        $serverParams = self::now()->getServerParams();
        return isset($serverParams['remote_addr']) ? $serverParams['remote_addr'] : null;
    }
}
