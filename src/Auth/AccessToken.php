<?php

namespace Iit\HyLib\Auth;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Iit\HyLib\Utils\Log;
use Iit\HyLib\Utils\Req;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class AccessToken
{
    /**
     * @var string
     */

    protected $headerKey;

    /**
     * @var string
     */

    protected $cachePrefix;

    /**
     * @var int
     */

    protected $expired;

    /**
     * @var bool
     */

    protected $autoRefresh;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * ApiToken constructor.
     * @param $config
     */

    public function __construct($config)
    {
        if (!isset($config['token_header_key']) || empty($config['token_header_key'])) {
            throw new \RuntimeException('config "tokenHeaderKey" can\'t empty.');
        }
        if (!isset($config['cache_prefix']) || empty($config['cache_prefix'])) {
            throw new \RuntimeException('config key "cachePrefix" can\'t empty.');
        }
        $this->cache = ApplicationContext::getContainer()->get(CacheInterface::class);
        $this->headerKey = $config['token_header_key'];
        $this->cachePrefix = $config['cache_prefix'];
        $this->expired = isset($config['token_expired']) ? $config['token_expired'] : 120;
        $this->autoRefresh = isset($config['auto_refresh']) ? $config['auto_refresh'] : true;
    }

    /**
     * @return array|string
     */
    public function getToken()
    {
        return Context::getOrSet('xAccessToken', function () {
            return Req::now()->getHeaderLine($this->headerKey);
        });
    }

    /**
     * @return null|string
     */
    protected function getCode(): ?string
    {
        try {
            return $this->cache->get($this->cachePrefix . $this->getToken());
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * @return null|string
     */
    public function getUserCode(): ?string
    {
        $userCode = $this->getCode();
        $this->autoRefresh === true && !empty($userCode) && $this->cacheUserCode($userCode);
        return $userCode;
    }

    /**
     * @return bool
     */
    public function clearToken(): bool
    {
        try {
            return $this->cache->delete($this->cachePrefix . $this->getToken());
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * 生成随机token
     */
    protected function generateToken(): AccessToken
    {
        Context::set('xAccessToken', Str::random(40));
        return $this;
    }

    /**
     * @param $userCode
     * @return bool
     */
    public function setUserCode($userCode): bool
    {
        try {
            return $this->generateToken()->cacheUserCode($userCode);
        } catch (Throwable $exception) {
            Log::error($exception, ['exception' => $exception->__toString()]);
            return false;
        }
    }

    /**
     * @param $userCode
     * @return bool
     */
    protected function cacheUserCode($userCode): bool
    {
        try {
            return $this->cache->set($this->cachePrefix . $this->getToken(), $userCode, $this->expired * 60);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
