<?php
declare(strict_types=1);

namespace Iit\HyLib\Logger;

/**
 * Class StreamCustomJsonFormatter
 * @package Iit\HyLib\Logger
 */
class StreamCustomJsonFormatter extends CustomJsonFormatter
{
    /**
     * @param array $record
     * @return string
     */
    public function format(array $record): string
    {
        return $this->convertToJson($record) . "\n";
    }
}
