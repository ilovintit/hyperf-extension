<?php

namespace Iit\HyLib\Logger;

use Iit\HyLib\Utils\Log;
use Exception;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Coroutine;
use Monolog\Formatter\FormatterInterface;
use Throwable;

/**
 * Class CustomJsonFormatter
 * @package Iit\HyLib\Logger
 */
class CustomJsonFormatter implements FormatterInterface
{
    const SEQUENCE_CONTEXT_KEY = 'loggerSequence';

    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     * @return mixed The formatted record
     * @throws Exception
     */
    public function format(array $record)
    {
        return $this->convertToJson($record);
    }

    /**
     * @param array $record
     * @return false|string
     */

    protected function convertToJson(array $record)
    {
        $seq = Context::getOrSet(self::SEQUENCE_CONTEXT_KEY, 0);
        $baseFormat = [
            'id' => Log::id(),
            'lts' => strtolower($record['level_name']) . '/' . $record['datetime']->format('Y-m-d H:i:s.u') . '/' . $seq,
            'msg' => $record['message'],
        ];
        $exception = isset($record['context']['exception']) ? $record['context']['exception'] : null;
        if ($exception instanceof Throwable) {
            unset($record['context']['exception']);
            $baseFormat['nex'] = $exception->__toString();
        }
        $cxt = $this->getContext($record);
        if (!empty($cxt)) {
            $baseFormat['ncx'] = $cxt;
        }
        Context::set(self::SEQUENCE_CONTEXT_KEY, $seq + 1);
        return json_encode($baseFormat, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     * @return array The formatted set of records
     * @throws Exception
     */
    public function formatBatch(array $records): array
    {
        $message = [];
        foreach ($records as $record) {
            $message[] = $this->format($record);
        }
        return $message;
    }

    /**
     * @param $record
     * @param string $key
     * @return array|string
     */
    protected function getContext($record, $key = 'context')
    {
        if (!isset($record[$key])) {
            return "";
        }
        $context = $record[$key];
        if (empty($context)) {
            return "";
        } else if (is_array($context)) {
            return $context;
        } elseif (is_string($context)) {
            return $context;
        } elseif (is_object($context) && $context instanceof Arrayable) {
            return $context->toArray();
        } elseif (is_object($context) && $context instanceof Jsonable) {
            return json_decode($context->toJson(), true);
        } else {
            return "";
        }
    }
}
