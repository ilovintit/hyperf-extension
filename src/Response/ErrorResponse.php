<?php
declare(strict_types=1);

namespace Iit\Response;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Iit\HyLib\Exceptions\Exception;
use Iit\HyLib\Utils\Arr;
use Iit\HyLib\Utils\Log;
use Iit\HyLib\Utils\Res;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ErrorResponse
 * @package Iit\Response
 */
class ErrorResponse implements ResInterface
{
    /**
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response;

    /**
     * ErrorResponse constructor.
     * @param $message
     * @param int $code
     * @param array $data
     * @param int $statusCode
     * @param array $headers
     * @param int $encodingOptions
     */
    public function __construct($message, $code = 1, $data = [], $statusCode = 500, $headers = [], $encodingOptions = JSON_UNESCAPED_UNICODE)
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
        $this->response = Res::now()->withBody(new SwooleStream(Json::encode($returnData, $encodingOptions)))
            ->withAddedHeader('x-error-code', $returnData['code'])
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withStatus($statusCode);
        foreach ($headers as $key => $header) {
            $this->response = $this->response->withHeader($key, $header);
        }
    }

    public function toResponse(): ResponseInterface
    {
        return $this->response;
    }
}
