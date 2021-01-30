<?php
declare(strict_types=1);

namespace App\Extension\Utils;

use Ramsey\Uuid\Uuid;

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
