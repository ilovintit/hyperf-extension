<?php

declare(strict_types=1);

namespace Iit\HyLib\Exceptions\Handler;

use Iit\HyLib\Auth\Exception\AuthenticationException;
use Iit\HyLib\Exceptions\CustomException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        logs()->error(sprintf('%s [%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()), [
            'exception' => $throwable
        ]);
        if ($throwable instanceof CustomException) {
            return errors($throwable);
        }
        if ($throwable instanceof ValidationException) {
            return errors($body = $throwable->validator->errors()->first());
        }
        if ($throwable instanceof AuthenticationException) {
            return errors(trans('framework.auth.unauthenticated'), 401);
        }
        return errors($throwable->getMessage(), $throwable->getCode());
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
