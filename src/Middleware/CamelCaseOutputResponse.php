<?php
declare(strict_types=1);

namespace Iit\HyLib\Middleware;

/**
 * Class CamelCaseOutputResponse
 * @package Iit\HyLib\Middleware
 */
class CamelCaseOutputResponse extends FormatConvertBody
{
    protected ?string $outputConvertFormat = self::FORMAT_CAMEL_CASE;
}
