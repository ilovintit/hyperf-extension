<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Iit\HyLib\Auth\AccessToken;
use Iit\HyLib\Auth\Contract\Authenticatable;
use Iit\HyLib\Auth\Contract\Guard;
use Iit\HyLib\Auth\Contract\HttpAuthContract;

class Auth
{
    /**
     * @param null $guard
     * @return Guard
     */
    public static function guard($guard = null): Guard
    {
        return ApplicationContext::getContainer()->get(HttpAuthContract::class)->guard($guard);
    }

    /**
     * @param null $guard
     * @return Authenticatable|null
     */
    public static function user($guard = null): ?Authenticatable
    {
        return self::guard($guard)->user();
    }

    /**
     * @param null $guard
     * @return bool
     */
    public static function guest($guard = null): bool
    {
        return self::guard($guard)->guest();
    }

    /**
     * @param null $guard
     * @return bool
     */
    public static function check($guard = null): bool
    {
        return self::guard($guard)->check();
    }

    /**
     * @return string
     */
    public static function getAccessToken(): string
    {
        return Context::get(AccessToken::ACCESS_TOKEN_KEY);
    }

    /**
     * @param string $accessToken
     * @return string
     */
    public static function setAccessToken(string $accessToken): string
    {
        return Context::set(AccessToken::ACCESS_TOKEN_KEY, $accessToken);
    }
}
