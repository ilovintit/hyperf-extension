<?php
declare(strict_types=1);

namespace Iit\HyLib\Middleware;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Iit\HyLib\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class FormatConvertBody
 * @package Iit\HyLib\Middleware
 */
class FormatConvertBody implements MiddlewareInterface
{
    const FORMAT_CAMEL_CASE = 'camelCaseArrayKeys';
    const FORMAT_SNAKE_CASE = 'snakeCaseArrayKeys';
    const FORMAT_STUDLY_CASE = 'studlyCaseArrayKeys';

    /**
     * @var string|null
     */
    protected ?string $inputConvertFormat = null;

    /**
     * @var string|null
     */
    protected ?string $outputConvertFormat = null;

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $formatList = [self::FORMAT_CAMEL_CASE, self::FORMAT_SNAKE_CASE, self::FORMAT_STUDLY_CASE];
        //检查是否开启输入格式转换
        if (in_array($this->inputConvertFormat, $formatList)) {
            $reqBody = $request->getParsedBody();
            if (!empty($reqBody)) {
                $method = $this->inputConvertFormat;
                $request = Context::set(ServerRequestInterface::class, $request->withParsedBody(Arr::$method($reqBody)));
            }
        }
        $response = $handler->handle($request);
        //检查是否开启输出格式转换
        if (in_array($this->outputConvertFormat, $formatList)) {
            $resContent = $response->getBody()->getContents();
            if (!empty($resContent) && !empty(json_decode($resContent, true))) {
                $method = $this->outputConvertFormat;
                Context::set(ResponseInterface::class, $response
                    ->withBody(new SwooleStream(Json::encode(Arr::$method(Json::decode($resContent, true))))));
            }
        }
        return $response;
    }
}
