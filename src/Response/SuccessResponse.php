<?php
declare(strict_types=1);

namespace Iit\HyLib\Response;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Iit\HyLib\Utils\Arr;
use Iit\HyLib\Utils\Log;
use Iit\HyLib\Utils\Res;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SuccessRes
 * @package Iit\Response
 */
class SuccessResponse implements ResInterface
{
    /**
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response;

    /**
     * SuccessResponse constructor.
     * @param array $data
     * @param null $message
     * @param array $headers
     * @param int $statusCode
     * @param int $encodingOptions
     */
    public function __construct($data = [], $message = null, $headers = [], $statusCode = 200, $encodingOptions = JSON_UNESCAPED_UNICODE)
    {
        $returnData = [
            'request' => Log::id(),
            'code' => 0,
            'message' => $message === null ? trans('framework.response.success') : $message,
            'data' => is_string($data) ? $data : (is_array($data) ? Arr::filterNull($data) : Arr::filterNull((collect($data)->toArray()))),
        ];
        $this->response = Res::now()->withBody(new SwooleStream(Json::encode($returnData, $encodingOptions)))
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus($statusCode);
        foreach ($headers as $key => $header) {
            $this->response = $this->response->withHeader($key, $header);
        }
    }

    /**
     * @return ResponseInterface
     */
    public function toResponse(): ResponseInterface
    {
        return $this->response;
    }
}
