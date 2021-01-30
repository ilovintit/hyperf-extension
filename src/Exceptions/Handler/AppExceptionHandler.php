<?php

declare(strict_types=1);

namespace Iit\HyLib\Exceptions\Handler;

use Iit\HyLib\Auth\Exception\AuthenticationException;
use Iit\HyLib\Exceptions\CustomException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException;
use Iit\HyLib\Utils\Log;
use Iit\HyLib\Utils\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class AppExceptionHandler
 * @package Iit\HyLib\Exceptions\Handler
 */
class AppExceptionHandler extends ExceptionHandler
{

    /**
     * @param Throwable $throwable
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        Log::error(sprintf('%s [%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()), ['exception' => $throwable]);
        if ($throwable instanceof CustomException) {
            return Response::error($throwable);
        }
        if ($throwable instanceof ValidationException) {
            return Response::error($body = $throwable->validator->errors()->first());
        }
        if ($throwable instanceof AuthenticationException) {
            return Response::error(trans('framework.auth.unauthenticated'), 401);
        }
        return Response::error($throwable->getMessage(), $throwable->getCode());
    }

    /**
     * @param Throwable $throwable
     * @return bool
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
