<?php
declare(strict_types=1);

namespace Iit\HyLib\Parser;

use Hyperf\HttpMessage\Server\Request\JsonParser;

/**
 * Class RequestJsonParser
 * @package Iit\HyLib\Request
 */
class RequestJsonParser extends JsonParser
{
    public function parse(string $rawBody, string $contentType): array
    {
        return empty($rawBody) ? [] : parent::parse($rawBody, $contentType);
    }
}
