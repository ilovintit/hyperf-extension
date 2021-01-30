<?php

declare(strict_types=1);

namespace Iit\HyLib\Middleware;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Iit\HyLib\Utils\Arr;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CamelCaseOutputResponse
 * @package Iit\HyLib\Middleware
 */
class CamelCaseOutputResponse implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * GenRequestId constructor.
     * @param ContainerInterface $container
     */

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $returnContent = $response->getBody()->getContents();
        if (!empty($returnContent) && !empty(json_decode($returnContent, true))) {
            Context::set(ResponseInterface::class, $response
                ->withBody(new SwooleStream(Json::encode(Arr::camelCaseArrayKeys(Json::decode($returnContent, true))))));
        }
        return $response;
    }
}
