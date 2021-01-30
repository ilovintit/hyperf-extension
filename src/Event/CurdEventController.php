<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Exceptions\CustomException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Iit\HyLib\Utils\Event;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class CurdEventController
 * @package Iit\HyLib\Contracts
 */
abstract class CurdEventController extends AbstractController implements EventController
{
    /**
     * @param $eventName
     * @param mixed ...$params
     * @return AbstractEvent
     */

    protected function newEventClass($eventName, ...$params): AbstractEvent
    {
        $eventName = empty($this->namespace()) ? $eventName : $this->namespace() . '\\' . $eventName;
        $event = new $eventName(...$params);
        if (!$event instanceof AbstractEvent) {
            throw new CustomException('Event Must Extend App\\Extension\\AbstractEvent Class.');
        }
        return $event;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */

    public function getList(RequestInterface $request): ResponseInterface
    {
        return Event::trigger($this->newEventClass('QueryList', $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */

    public function exportList(RequestInterface $request): ResponseInterface
    {
        return Event::trigger($this->newEventClass('ExportList', $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */

    public function postInfo(RequestInterface $request): ResponseInterface
    {
        return Event::triggerWithTransaction($this->newEventClass('Create', $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     */

    public function getInfo(RequestInterface $request, string $code): ResponseInterface
    {
        return Event::trigger($this->newEventClass('Query', $code, $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putInfo(RequestInterface $request, string $code): ResponseInterface
    {
        return Event::triggerWithTransaction($this->newEventClass('Update', $code, $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putEnabled(RequestInterface $request, string $code): ResponseInterface
    {
        return Event::triggerWithTransaction($this->newEventClass('Enabled', $code, $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putDisabled(RequestInterface $request, string $code): ResponseInterface
    {
        return Event::triggerWithTransaction($this->newEventClass('Disabled', $code, $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function deleteInfo(RequestInterface $request, string $code): ResponseInterface
    {
        return Event::triggerWithTransaction($this->newEventClass('Delete', $code, $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putRestore(RequestInterface $request, string $code): ResponseInterface
    {
        return Event::triggerWithTransaction($this->newEventClass('Restore', $code, $request->getHeaders()))->toResponse();
    }

}
