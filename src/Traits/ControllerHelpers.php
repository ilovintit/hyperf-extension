<?php
declare(strict_types=1);

namespace App\Extension\Traits;

use App\Utils\Event;
use Psr\Http\Message\ResponseInterface;
use Throwable;

trait ControllerHelpers
{
    /**
     * @param array $definitionLabels
     * @return ResponseInterface
     */

    public function staticDefinition(array $definitionLabels)
    {
        return success(definition_to_select($definitionLabels));
    }

    /**
     * @param $event
     * @return ResponseInterface
     * @throws Throwable
     */

    public function eventTransaction($event)
    {
        return Event::triggerWithTransaction($event)->toResponse();
    }


    public function event($event)
    {

    }
}
