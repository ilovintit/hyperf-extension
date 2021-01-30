<?php

declare(strict_types=1);

namespace Iit\HyLib\Auth;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\InteractsWithTime;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class RateLimiter
 * @package Iit\HyLib\Auth
 */
class RateLimiter
{
    use InteractsWithTime;

    /**
     * @Inject
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @param $key
     * @param $maxAttempts
     * @return bool
     * @throws InvalidArgumentException
     */
    public function tooManyAttempts($key, $maxAttempts): bool
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($key . ':timer')) {
                return true;
            }
            $this->resetAttempts($key);
        }
        return false;
    }

    /**
     * @param $key
     * @param int $decaySeconds
     * @return int
     * @throws InvalidArgumentException
     */
    public function hit($key, $decaySeconds = 60): int
    {
        $this->cache->set(
            $key . ':timer',
            $this->availableAt($decaySeconds),
            $decaySeconds
        );
        $added = $this->cache->set($key, 0, $decaySeconds);
        $hits = (int)$this->cache->get($key);
        if (!$added && $hits == 1) {
            $this->cache->set($key, 1, $decaySeconds);
        }
        return $hits;
    }

    /**
     * @param $key
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function attempts($key)
    {
        return $this->cache->get($key, 0);
    }

    /**
     * @param $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function resetAttempts($key): bool
    {
        return $this->cache->delete($key);
    }

    /**
     * @param $key
     * @param $maxAttempts
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function retriesLeft($key, $maxAttempts)
    {
        $attempts = $this->attempts($key);
        return $maxAttempts - $attempts;
    }

    /**
     * @param $key
     * @throws InvalidArgumentException
     */
    public function clear($key)
    {
        $this->resetAttempts($key);
        $this->cache->delete($key . ':timer');
    }

    /**
     * @param $key
     * @return int|mixed
     * @throws InvalidArgumentException
     */
    public function availableIn($key): int
    {
        return $this->cache->get($key . ':timer') - $this->currentTime();
    }
}
