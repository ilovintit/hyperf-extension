<?php
declare(strict_types=1);

namespace Iit\HyLib\Exceptions;

/**
 * Class CustomException
 * @package Iit\HyLib\Exceptions
 */
class CustomException extends Exception
{
    /**
     * @var string
     */
    protected string $customMessage;

    /**
     * @var int
     */
    protected int $errorCode;

    /**
     * @var int
     */
    protected int $statusCode;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @var array
     */
    protected array $headers;

    /**
     * @var array
     */
    protected array $debug;

    /**
     * CustomException constructor.
     * @param $message
     * @param int $errorCode
     * @param int $statusCode
     * @param array $data
     * @param array $headers
     * @param array $debug
     */
    public function __construct($message, $errorCode = 1, $statusCode = 500, $data = [], $headers = [], $debug = [])
    {
        $this->customMessage = $message;
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->headers = $headers;
        $this->debug = $debug;
        parent::__construct();
    }

    /**
     * 唯一错误代码5位数字，不能以零开头
     *
     * @return integer
     */
    protected function errorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 错误信息提示
     *
     * @return string
     */
    protected function message(): string
    {
        return $this->customMessage;
    }

    /**
     * 固定调试信息
     *
     * @return array|null
     */
    protected function debug(): ?array
    {
        return $this->debug;
    }

    /**
     * Http状态码
     *
     * @return int
     */
    protected function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 头部信息
     *
     * @return array
     */
    protected function headers(): array
    {
        return $this->headers;
    }

    /**
     * 内容信息
     *
     * @return array|null
     */
    protected function data(): ?array
    {
        return $this->data;
    }
}
