<?php
declare(strict_types=1);

namespace Iit\HyLib\Auth;

use Iit\HyLib\Auth\Exception\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * 如果有登录则登录，无则允许继续访问
 * Class AnonymousAuthMiddleware
 * @package Iit\HyLib\Auth
 */
class AnonymousAuthMiddleware extends AuthenticateMiddleware
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->authenticate($request, $this->guards());
        } catch (AuthenticationException $exception) {
            return $handler->handle($request);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            return $handler->handle($request);
        }
    }
}
