<?php
declare(strict_types=1);

namespace Iit\HyLib\RedisLock;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;
use Redis;

/**
 * Class RedisLock
 * @package Iit\HyLib\RedisLock
 */
class RedisLock extends AbstractLock
{
    /**
     * @var RedisProxy|Redis|null
     */

    protected $redis;

    /**
     * Create a new lock instance.
     *
     * @param string $redis
     * @param string $name
     * @param int $seconds
     * @param string|null $owner
     */
    public function __construct(string $redis, string $name, int $seconds, $owner = null)
    {
        parent::__construct(config('cache.default.prefix') . 'lock:' . $name, $seconds, $owner);
        $this->redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get($redis);
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    public function acquire(): bool
    {
        if ($this->seconds > 0) {
            return $this->redis->set($this->name, $this->owner, ['NX', 'EX' => $this->seconds]) == true;
        } else {
            return $this->redis->setnx($this->name, $this->owner) === 1;
        }
    }

    /**
     * Release the lock.
     *
     * @return bool
     */
    public function release(): bool
    {
        return (bool)$this->redis->eval(LuaScripts::releaseLock(), [$this->name, $this->owner], 1);
    }

    /**
     * Releases this lock in disregard of ownership.
     *
     * @return bool
     */
    public function forceRelease(): bool
    {
        return (bool)$this->redis->del($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return string
     */
    protected function getCurrentOwner(): string
    {
        return $this->redis->get($this->name);
    }

    /**
     * @param $name
     * @param $seconds
     * @param null $owner
     * @return RedisLock
     */

    public static function create($name, $seconds = 0, $owner = null): RedisLock
    {
        return make(static::class, [
            'redis' => config('redis.lock.pool', 'default'),
            'name' => $name,
            'seconds' => $seconds,
            'owner' => $owner
        ]);
    }
}
