<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Iit\HyLib\RedisLock\RedisLock;

trait EventLockHelper
{
    public RedisLock $lock;

    /**
     * @return \Iit\HyLib\RedisLock\RedisLock
     */
    public function getLock(): RedisLock
    {
        return $this->lock;
    }
}
