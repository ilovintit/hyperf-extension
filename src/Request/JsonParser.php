<?php
declare(strict_types=1);

namespace App\Extension\Request;

class JsonParser extends \Hyperf\HttpMessage\Server\Request\JsonParser
{
    public function parse(string $rawBody, string $contentType): array
    {
        return empty($rawBody) ? [] : parent::parse($rawBody, $contentType);
    }
}
