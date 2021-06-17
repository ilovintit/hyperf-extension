<?php

namespace Iit\HyLib\Utils;

use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;
use Iit\HyLib\Event\AbstractEvent;
use Iit\HyLib\Event\EventLock;
use Iit\HyLib\Exceptions\CustomException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * Class Event
 * @package Iit\HyLib\Utils
 */
class Event
{
    /**
     * @param AbstractEvent $event
     * @return object|AbstractEvent
     */

    public static function trigger(AbstractEvent $event)
    {
        return ApplicationContext::getContainer()->get(EventDispatcherInterface::class)->dispatch($event);
    }

    /**
     * @param AbstractEvent $event
     * @return AbstractEvent|object
     * @throws Throwable
     */

    public static function triggerWithTransaction(AbstractEvent $event)
    {
        Db::beginTransaction();
        try {
            if ($event instanceof EventLock && !$event->getLock()->get()) {
                throw new CustomException($event->getLockFailMessage());
            }
            $resultEvent = self::trigger($event);
            if ($event instanceof EventLock && !$event->getLock()->release()) {
                throw new CustomException('事务操作超时,已有其他进程处理此任务');
            }
            Db::commit();
            return $resultEvent;
        } catch (Throwable $exception) {
            Db::rollBack();
            $event instanceof EventLock && $event->getLock()->release();
            throw $exception;
        }
    }
}
