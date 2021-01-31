<?php
declare(strict_types=1);

namespace Iit\HyLib\Controller;

use Iit\HyLib\Utils\Event;
use Iit\HyLib\Utils\Res;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Trait ControllerHelpers
 * @package Iit\HyLib\Controller
 */
trait ControllerHelpers
{
    /**
     * @param array $definitionLabels
     * @return ResponseInterface
     */
    public function staticDefinition(array $definitionLabels): ResponseInterface
    {
        return Res::success(Res::definitionToSelect($definitionLabels));
    }

    /**
     * @param $event
     * @return ResponseInterface
     * @throws Throwable
     */
    public function eventTransaction($event): ResponseInterface
    {
        return Event::triggerWithTransaction($event)->toResponse();
    }

    /**
     * @param $event
     * @return ResponseInterface
     */
    public function event($event): ResponseInterface
    {
        return Event::trigger($event)->toResponse();
    }
}
