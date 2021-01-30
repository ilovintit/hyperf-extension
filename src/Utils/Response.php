<?php
declare(strict_types=1);

namespace App\Extension\Utils;


use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @return ResponseInterface|null
     */
    public static function now(): ?ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }

    public static function successResponse()
    {

    }

    public static function errorResponse()
    {

    }
}
