<?php
declare(strict_types=1);

namespace Iit\HyLib\Middleware;

use App\Utils\Encryption;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DecryptionBody implements MiddlewareInterface
{

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
        if (!config('tools.transmission_encryption')) {
            return $handler->handle($request);
        }
        $requestBody = $request->getParsedBody();
        if (empty($requestBody)) {
            return $handler->handle($request);
        }
        $decodeBody = json_decode(Encryption::decode($requestBody['encryptionData']), true);
        logs()->info('decryption-body-success', ['body' => $decodeBody]);
        return $handler->handle(Context::set(ServerRequestInterface::class, $request->withParsedBody($decodeBody)));
    }
}
