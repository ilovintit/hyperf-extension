<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Iit\HyLib\Exceptions\Exception;
use Iit\Response\ErrorResponse;
use Iit\Response\SuccessResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 * @package Iit\HyLib\Utils
 */
class Res
{
    /**
     * @return ResponseInterface|null
     */
    public static function now(): ?ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }

    /**
     * @param array $definition
     * @param string $keyName
     * @param string $valueName
     * @return array
     */
    public static function definitionToSelect(array $definition, $keyName = 'code', $valueName = 'name'): array
    {
        $returnArr = [];
        foreach ($definition as $key => $value) {
            $returnArr[] = [$keyName => $key, $valueName => $value];
        }
        return $returnArr;
    }

    /**
     * @param array $data
     * @param null $message
     * @param array $headers
     * @param int $statusCode
     * @param int $encodingOptions
     * @return ResponseInterface
     */
    public static function success($data = [], $message = null, $headers = [], $statusCode = 200, $encodingOptions = JSON_UNESCAPED_UNICODE): ResponseInterface
    {
        return make(SuccessResponse::class, [$data, $message, $headers, $statusCode, $encodingOptions])->toResponse();
    }

    /**
     * @param $message
     * @param int $code
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @param int $encodingOptions
     * @return ResponseInterface
     */
    public static function error($message, $code = 1, $data = [], $statusCode = 500, $headers = [], $encodingOptions = JSON_UNESCAPED_UNICODE): ResponseInterface
    {
        return make(ErrorResponse::class, [$message, $code, $data, $statusCode, $headers, $encodingOptions])->toResponse();
    }
}
