<?php
declare(strict_types=1);

namespace Iit\HyLib\RedisLock;

use Hyperf\Utils\Str;

/**
 * Class AbstractLock
 * @package Iit\HyLib\RedisLock
 */
abstract class AbstractLock implements Lock
{
    use InteractsWithTime;

    /**
     * The name of the lock.
     *
     * @var string
     */
    protected string $name;

    /**
     * The number of seconds the lock should be maintained.
     *
     * @var int
     */
    protected int $seconds;

    /**
     * The scope identifier of this lock.
     *
     * @var ?string
     */
    protected ?string $owner;

    /**
     * Create a new lock instance.
     *
     * @param string $name
     * @param int $seconds
     * @param string|null $owner
     */
    public function __construct(string $name, int $seconds, $owner = null)
    {
        if (is_null($owner)) {
            $owner = Str::random();
        }
        $this->name = $name;
        $this->owner = $owner;
        $this->seconds = $seconds;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @return bool
     */
    abstract public function acquire(): bool;

    /**
     * Release the lock.
     *
     * @return bool
     */
    abstract public function release(): bool;

    /**
     * Returns the owner value written into the driver for this lock.
     *
     * @return string
     */
    abstract protected function getCurrentOwner(): string;

    /**
     * Attempt to acquire the lock.
     *
     * @param callable|null $callback
     * @return mixed
     */
    public function get($callback = null): bool
    {
        $result = $this->acquire();
        if ($result && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }
        return $result;
    }

    /**
     * Attempt to acquire the lock for the given number of seconds.
     *
     * @param int $seconds
     * @param callable|null $callback
     * @return bool
     *
     * @throws LockTimeoutException
     */
    public function block(int $seconds, $callback = null): bool
    {
        $starting = $this->currentTime();
        while (!$this->acquire()) {
            usleep(250 * 1000);
            if ($this->currentTime() - $seconds >= $starting) {
                throw new LockTimeoutException;
            }
        }
        if (is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }
        return true;
    }

    /**
     * Returns the current owner of the lock.
     *
     * @return string
     */
    public function owner(): string
    {
        return $this->owner;
    }

    /**
     * Determines whether this lock is allowed to release the lock in the driver.
     *
     * @return bool
     */
    protected function isOwnedByCurrentProcess(): bool
    {
        return $this->getCurrentOwner() === $this->owner;
    }
}
