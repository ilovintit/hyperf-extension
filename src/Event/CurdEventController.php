<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Exceptions\CustomException;
use App\Utils\Event;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class CurdEventController extends AbstractController implements EventController
{
    /**
     * @param $eventName
     * @param mixed ...$params
     * @return AbstractEvent|null
     */

    protected function newEventClass($eventName, ...$params)
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

    public function getList(RequestInterface $request)
    {
        return Event::trigger($this->newEventClass('QueryList', $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */

    public function exportList(RequestInterface $request)
    {
        return Event::trigger($this->newEventClass('ExportList', $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */

    public function postInfo(RequestInterface $request)
    {
        return Event::triggerWithTransaction($this->newEventClass('Create', $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     */

    public function getInfo(RequestInterface $request, string $code)
    {
        return Event::trigger($this->newEventClass('Query', $code, $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putInfo(RequestInterface $request, string $code)
    {
        return Event::triggerWithTransaction($this->newEventClass('Update', $code, $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putEnabled(RequestInterface $request, string $code)
    {
        return Event::triggerWithTransaction($this->newEventClass('Enabled', $code, $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putDisabled(RequestInterface $request, string $code)
    {
        return Event::triggerWithTransaction($this->newEventClass('Disabled', $code, $request->all(), $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function deleteInfo(RequestInterface $request, string $code)
    {
        return Event::triggerWithTransaction($this->newEventClass('Delete', $code, $request->getHeaders()))->toResponse();
    }

    /**
     * @param RequestInterface $request
     * @param string $code
     * @return ResponseInterface
     * @throws Throwable
     */

    public function putRestore(RequestInterface $request, string $code)
    {
        return Event::triggerWithTransaction($this->newEventClass('Restore', $code, $request->getHeaders()))->toResponse();
    }

}
