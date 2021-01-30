<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Ramsey\Uuid\Uuid;

/**
 * Class H
 * @package Iit\HyLib\Utils
 */
class H
{
    /**
     * @return string
     */
    public static function uuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}
