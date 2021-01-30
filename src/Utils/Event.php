<?php

namespace Iit\HyLib\Utils;

use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;
use Iit\HyLib\Contracts\AbstractEvent;
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
            $resultEvent = self::trigger($event);
            Db::commit();
            return $resultEvent;
        } catch (Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }
    }
}
