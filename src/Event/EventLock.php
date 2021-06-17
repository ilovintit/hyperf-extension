<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Iit\HyLib\RedisLock\RedisLock;

interface EventLock
{
    /**
     * @return \Iit\HyLib\RedisLock\RedisLock
     */
    public function getLock(): RedisLock;

    /**
     * @return string
     */
    public function getLockFailMessage(): string;
}
