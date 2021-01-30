<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Iit\HyLib\Exceptions\Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 * @package Iit\HyLib\Utils
 */
class Response
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
        $returnData = [
            'request' => Log::id(),
            'code' => 0,
            'message' => $message === null ? trans('framework.response.success') : $message,
            'data' => is_string($data) ? $data : (is_array($data) ? Arr::filterNull($data) : Arr::filterNull((collect($data)->toArray()))),
        ];
        $response = self::now()->withBody(new SwooleStream(Json::encode($returnData, $encodingOptions)))
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus($statusCode);
        foreach ($headers as $key => $header) {
            $response = $response->withHeader($key, $header);
        }
        return $response;
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
        $returnData = [
            'request' => Log::id(),
            'code' => $code === null || intval($code) === 0 || intval($code) === 1 ? 99999 : $code,
            'message' => $message,
            'data' => is_string($data) ? $data : (is_array($data) ? Arr::filterNull($data) : Arr::filterNull((collect($data)->toArray()))),
        ];
        if ($message instanceof Exception) {
            $returnData['code'] = $message->getErrorCode();
            $returnData['message'] = $message->getMessage();
            $returnData['data'] = $message->getData();
            $returnData['exception'] = get_class($message);
            $statusCode = $message->getStatusCode();
            $headers = $message->getHeaders();
        }
        $response = self::now()->withBody(new SwooleStream(Json::encode($returnData, $encodingOptions)))
            ->withAddedHeader('x-error-code', $returnData['code'])
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus($statusCode);
        foreach ($headers as $key => $header) {
            $response = $response->withHeader($key, $header);
        }
        return $response;
    }
}
