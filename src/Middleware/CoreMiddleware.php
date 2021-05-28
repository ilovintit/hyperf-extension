<?php

declare(strict_types=1);

namespace Iit\HyLib\Middleware;

use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Utils\Contracts\Arrayable;
use Iit\HyLib\Utils\Res;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CoreMiddleware
 * @package Iit\HyLib\Middleware
 */
class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    /**
     * @param Dispatched $dispatched
     * @param ServerRequestInterface $request
     * @return array|Arrayable|mixed|ResponseInterface|string
     */

    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request)
    {
        $response = parent::handleFound($dispatched, $request);
        if ($response instanceof Response) {
            return $response->getContent();
        }
        if (empty($response)) {
            return $response;
        }
        if ($response->getStatusCode() === 500 && !$response->hasHeader('x-error-code')) {
            return Res::error(trans('framework.response.handler-not-exists'));
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */

    protected function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        return Res::error(trans('framework.response.not-found'), 1, [], 404);
    }

    /**
     * @param array $methods
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */

    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request): ResponseInterface
    {
        return Res::error(trans('framework.response.method-not-allow'), 1, ['allow' => $methods], 405);
    }

}
