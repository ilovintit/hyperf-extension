<?php

declare(strict_types=1);

namespace Iit\HyLib\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Hyperf\Event\Contract\ListenerInterface;
use Iit\HyLib\Utils\Log;

/**
 * Class DbQueryListener
 * @package App\Listener\Listener
 */
class DbQueryListener implements ListenerInterface
{

    /**
     * @return array|string[]
     */

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param object $event
     */

    public function process(object $event)
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (!Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $key => $value) {
                    $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                }
            }
            Log::info(sprintf('[%s] %s', $event->time, $sql));
        }
    }
}
