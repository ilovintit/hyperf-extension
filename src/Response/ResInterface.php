<?php
declare(strict_types=1);

namespace Iit\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResInterface
 * @package Iit\Response
 */
interface ResInterface
{
    /**
     * @return ResponseInterface
     */
    public function toResponse(): ResponseInterface;
}
