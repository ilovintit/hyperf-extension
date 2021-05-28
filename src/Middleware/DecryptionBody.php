<?php
declare(strict_types=1);

namespace Iit\HyLib\Middleware;

use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Iit\HyLib\Utils\EAD;
use Iit\HyLib\Utils\Log;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class DecryptionBody
 * @package Iit\HyLib\Middleware
 */
class DecryptionBody implements MiddlewareInterface
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
        if (!$this->encryptionSwitch()) {
            return $handler->handle($request);
        }
        $requestBody = $request->getParsedBody();
        if (empty($requestBody)) {
            return $handler->handle($request);
        }
        $decodeBody = Json::decode($this->ead()->decode($requestBody['encryptionData']));
        Log::debug('decryption-body-info', ['decodeBody' => $decodeBody]);
        return $handler->handle(Context::set(ServerRequestInterface::class, $request->withParsedBody($decodeBody)));
    }

    /**
     * @return bool
     */
    protected function encryptionSwitch(): bool
    {
        return config('library.middleware.transmission_encryption');
    }

    /**
     * @return \Iit\HyLib\Utils\EAD
     */
    protected function ead(): EAD
    {
        return $this->container->get(EAD::class);
    }
}
