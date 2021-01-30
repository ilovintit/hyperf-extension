<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Iit\HyLib\Utils\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractEvent
 * @package Iit\HyLib\Contracts
 */
abstract class AbstractEvent
{
    /**
     * @var bool
     */
    public bool $result = true;

    /**
     * @var array
     */
    public array $returnData = [];

    /**
     * @var string|null
     */
    public ?string $returnMessage;

    /**
     * @var array
     */
    public array $returnHeaders = [];

    /**
     * @var int
     */
    public int $returnStatusCode;

    /**
     * @var int
     */
    public int $returnErrorCode = 99999;

    /**
     * @param array $data
     * @param null $message
     * @param array $headers
     * @param int $statusCode
     */
    public function returnSuccess($data = [], $message = null, $headers = [], $statusCode = 200)
    {
        $this->result = true;
        $this->returnData = $data;
        $this->returnMessage = $message;
        $this->returnHeaders = $headers;
        $this->returnStatusCode = $statusCode;
    }

    /**
     * @param null $message
     * @param array $data
     * @param int $errorCode
     * @param int $statusCode
     * @param array $headers
     */
    public function returnErrors($message = null, $data = [], $errorCode = 1, $statusCode = 500, $headers = [])
    {
        $this->result = false;
        $this->returnData = $data;
        $this->returnMessage = $message;
        $this->returnHeaders = $headers;
        $this->returnStatusCode = $statusCode;
        $this->returnErrorCode = $errorCode;
    }

    /**
     * @return ResponseInterface
     */
    public function toResponse(): ResponseInterface
    {
        if ($this->result === true) {
            return Response::success($this->returnData, $this->getMessage(), $this->returnHeaders, $this->returnStatusCode);
        }
        return Response::error($this->getMessage(), $this->returnErrorCode, $this->returnData, $this->returnStatusCode, $this->returnHeaders);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'result' => $this->result,
            'data' => $this->returnData,
            'message' => $this->returnMessage,
            'headers' => $this->returnHeaders,
            'status' => $this->returnStatusCode,
            'error' => $this->returnErrorCode
        ];
    }

    /**
     * @param array $returnData
     */
    public function fillReturnData(array $returnData)
    {
        $this->result = isset($returnData['result']) ? $returnData['result'] : $this->result;
        $this->returnData = isset($returnData['data']) ? $returnData['data'] : $this->returnData;
        $this->returnMessage = isset($returnData['message']) ? $returnData['message'] : $this->returnMessage;
        $this->returnHeaders = isset($returnData['headers']) ? $returnData['headers'] : $this->returnHeaders;
        $this->returnStatusCode = isset($returnData['status']) ? $returnData['status'] : $this->returnStatusCode;
        $this->returnErrorCode = isset($returnData['error']) ? $returnData['error'] : $this->returnErrorCode;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->returnMessage === null ? ($this->result === true ? $this->successMessage() : $this->failedMessage()) : $this->returnMessage;
    }

    /**
     * @return string
     */
    abstract public function successMessage(): string;

    /**
     * @return string
     */
    abstract public function failedMessage(): string;
}
