<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Utils\ApplicationContext;
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

    /**
     * @param $id
     * @return mixed
     */
    public static function app($id)
    {
        return ApplicationContext::getContainer()->get($id);
    }
}
