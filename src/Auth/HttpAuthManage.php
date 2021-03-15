<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Iit\HyLib\Auth;

use Iit\HyLib\Auth\Contract\Authenticatable;
use Iit\HyLib\Auth\Contract\Guard;
use Iit\HyLib\Auth\Contract\HttpAuthContract;
use Iit\HyLib\Auth\Contract\UserProvider;
use Iit\HyLib\Auth\Exception\InvalidArgumentException;
use Closure;
use Hyperf\Contract\ConfigInterface;

/**
 * @method bool check()
 * @method bool guest()
 * @method Authenticatable user()
 * @method string id()
 * @method string name()
 * @method bool validate(array $credentials = [])
 * @method  setUser(Authenticatable $user)
 * @method bool attempt(array $credentials = [], $remember = false)
 * @method bool once(array $credentials = [])
 * @method  login(Authenticatable $user, $remember = false)
 * @method Authenticatable loginUsingId($id, $remember = false)
 * @method bool onceUsingId($id)
 * @method  viaRemember()
 * @method  logout()
 */
class HttpAuthManage implements HttpAuthContract
{
    use ContextHelpers;

    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * HttpAuthManage constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }

    /**
     * @param null|string $name
     * @return Guard
     */
    public function guard($name = null): Guard
    {
        $name = $name ?: $this->getDefaultDriver();

        $guard = $this->getContext('guards::' . $name);

        return $guard ?: $this->setContext('guards::' . $name, $this->resolve($name));
    }

    /**
     * @param string $name
     */
    public function shouldUse($name)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);

        $this->resolveUsersUsing(function ($name = null) {
            return $this->guard($name)->user();
        });
    }

    /**
     * Get the user resolver callback.
     *
     * @return Closure
     */
    public function userResolver(): Closure
    {
        return $this->getContext('userResolver');
    }

    /**
     * Set the callback to be used to resolve users.
     *
     * @param Closure $userResolver
     * @return $this
     */
    public function resolveUsersUsing(Closure $userResolver): HttpAuthManage
    {
        $this->setContext('userResolver', $userResolver);

        return $this;
    }

    /**
     * @param $name
     */

    public function setDefaultDriver($name)
    {
        $this->setContext('defaults.guard', $name);
    }

    /**
     * @return mixed|null
     */

    public function getDefaultDriver()
    {
        return $this->getContext('defaults.guard') ?: $this->config->get('http-auth.defaults.guard');
    }

    /**
     * Create the user provider implementation for the driver.
     *
     * @param null|string $provider
     * @return null|UserProvider
     * @throws \InvalidArgumentException
     */
    public function createUserProvider($provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return null;
        }

        $driver = ($config['driver'] ?? null);

        if ($class = Config::getAnnotation($driver, UserProvider::class)) {
            // error_log("Use User Provider: [{$class}]");
            return make($class, [$config]);
        }
        throw new InvalidArgumentException(
            "Authentication user provider [{$driver}] is not defined."
        );
    }

    /**
     * Get the default user provider name.
     *
     * @return string
     */
    public function getDefaultUserProvider(): string
    {
        return $this->config->get('http-auth.defaults.provider');
    }

    /**
     * @param $name
     * @return mixed
     */

    protected function resolve($name)
    {
        $config = $this->config->get("http-auth.guards.{$name}");

        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }
        if ($class = Config::getAnnotation($config['driver'] ?? '', Guard::class)) {
            // error_log("Use Guard: [{$class}]");
            return make($class, [$config, $this->createUserProvider($config['provider'] ?? null)]);
        }
        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
        );
    }

    /**
     * Get the user provider configuration.
     *
     * @param null|string $provider
     * @return null|array
     */
    protected function getProviderConfiguration(?string $provider): ?array
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->config->get('http-auth.providers.' . $provider);
        }
    }
}
