<?php

namespace Iit\HyLib\Auth;

use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\SimpleCache\InvalidArgumentException;

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
        return Context::override('xAccessToken', function ($token) {
            return empty($token) ? request()->getHeaderLine($this->headerKey) : $token;
        });
    }

    /**
     * @return null|string
     */

    protected function getCode()
    {
        try {
            return cache()->get($this->cachePrefix . $this->getToken());
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * @return null|string
     */

    public function getUserCode()
    {
        $userCode = $this->getCode();
        $this->autoRefresh === true && !empty($userCode) && $this->cacheUserCode($userCode);
        return $userCode;
    }

    /**
     *
     */

    public function clearToken()
    {
        try {
            return cache()->delete($this->cachePrefix . $this->getToken());
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * 生成随机token
     */

    protected function generateToken()
    {
        Context::set('xAccessToken', Str::random(40));
        return $this;
    }

    /**
     * @param $userCode
     * @return bool
     */

    public function setUserCode($userCode)
    {
        try {
            return $this->generateToken()->cacheUserCode($userCode);
        } catch (\Exception $exception) {
            logs()->error($exception);
            return false;
        }
    }

    /**
     * @param $userCode
     * @return bool
     */

    protected function cacheUserCode($userCode)
    {
        try {
            return cache()->set($this->cachePrefix . $this->getToken(), $userCode, $this->expired * 60);
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
