<?php
declare(strict_types=1);

namespace Iit\HyLib\Manager;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;
use Iit\HyLib\Utils\Log;
use Iit\HyLib\Utils\Req;
use Redis;

class SessionManager
{

    /**
     * @var RedisProxy|Redis
     */
    public $redis;

    /**
     * SessionManager constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $redisPool = isset($config['pool']) ? $config['pool'] : 'default';
        $this->redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get($redisPool);
    }

    /**
     * @return string
     */
    public function cacheKey(): ?string
    {
        $sessionId = method_exists(Req::now(), 'getHeaderLine') ?
            Req::now()->getHeaderLine('X-Ca-Applets-Session-Id') : null;
        return empty($sessionId) ? null : $sessionId;
    }

    /**
     * @param $sessionKey
     */
    public function saveSessionKey($sessionKey)
    {
        if (!empty($this->cacheKey())) {
            $this->redis->set($this->cacheKey(), $sessionKey, 86400);
        }
    }

    /**
     * @return bool|mixed|string
     */
    public function getSession()
    {
        Log::info('session-cache-key', ['key' => $this->cacheKey()]);
        return !empty($this->cacheKey()) ? $this->redis->get($this->cacheKey()) : null;
    }
}
