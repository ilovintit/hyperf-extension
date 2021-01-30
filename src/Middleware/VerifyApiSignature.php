<?php

declare(strict_types=1);

namespace Iit\HyLib\Middleware;

use Iit\HyLib\Exceptions\CustomException;
use Carbon\Carbon;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class VerifyApiSignature implements MiddlewareInterface
{

    protected $requiredHeaders = [
        'X-Ca-Signature-Headers',
        'X-Ca-Timestamp',
        'X-Ca-Nonce',
        'X-Ca-Signature',
    ];

    protected $signElseHeaders = [
        'accept',
        'content-md5',
        'date',
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CacheInterface
     */

    protected $cache;

    /**
     * VerifyApiSignature constructor.
     * @param ContainerInterface $container
     */

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cache = $container->get(CacheInterface::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */

    protected function getContentEncode(ServerRequestInterface $request)
    {
        if (in_array($request->getMethod(), ['GET', 'DELETE'])) {
            return '';
        }
        return (empty($request->getBody()->getContents()) ? '' : base64_encode(md5($request->getBody()->getContents(), true)));
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /*
         * 检查签名必须的字段
         */
        foreach ($this->requiredHeaders as $headerKey) {
            if (!$request->hasHeader($headerKey)) {
                throw new CustomException(trans('middleware.signature.header_required', ['key' => $headerKey]));
            }
        }
        if (strlen($request->getHeaderLine('X-Ca-Nonce')) !== 36) {
            throw new CustomException(trans('middleware.signature.header_length_invalid', ['key' => 'X-Ca-Nonce', 'length' => 40, 'unit' => 'byte']));
        }
        /*
         * 检验请求的时间与实际时间的偏差值，超过偏差值的请求会被拒绝，防止回放攻击
         */
        try {
            $timeDiff = (new Carbon($request->getHeaderLine('X-Ca-Timestamp'), 'UTC'))->diffInSeconds(Carbon::now('UTC'), false);
        } catch (Exception $exception) {
            throw new CustomException(trans('middleware.signature.timestamp_format_invalid'));
        }
        if ($timeDiff > 900 || $timeDiff < -900) {
            throw new CustomException('middleware.signature.timestamp_invalid');
        }
        /*
         * 根据请求的路径和请求的随机数进行校验，保证在15分钟内只能请求一次，结合上述时间校验防止回放攻击
         */
        $uniqueRequestStr = 'signature:' . sha1($request->getUri()->getPath() . "\n" . $request->getHeaderLine('X-Ca-Nonce'));
        if ($this->cache->get($uniqueRequestStr)) {
            throw new CustomException(trans('middleware.signature.repeat_request'));
        }
        $this->cache->set($uniqueRequestStr, microtime(true), 900);
        /*
         * 组装签名字符串的请求头部分
         */
        $signHeaders = explode(',', $request->getHeaderLine('X-Ca-Signature-Headers'));
        sort($signHeaders);
        $signHeaderString = implode("\n", collect($signHeaders)->map(function ($headerKey) use ($request) {
            return $headerKey . ':' . $request->getHeaderLine($headerKey);
        })->toArray());
        /*
         * 组装签名字符串的query部分
         */
        $signQuery = $request->getQueryParams();
        ksort($signQuery);
        $signQueryString = implode('&', collect($signQuery)->map(function ($value, $key) {
            return $key . '=' . $value;
        })->toArray());
        /*
         * 组装签名字符串
         */
        $signString = strtoupper($request->getMethod()) . "\n"
            . $request->getHeaderLine('Content-Type') . "\n"
            . $this->getContentEncode($request) . "\n"
            . $request->getHeaderLine('Accept') . "\n"
            . $request->getHeaderLine('X-Ca-Timestamp') . "\n"
            . $signHeaderString . "\n"
            . $request->getUri()->getPath() . (empty($request->getQueryParams()) ? '' : '?' . $signQueryString);
        $signSecret = config('tools.api_signature_secret');
        /*
         * 计算签名并对比请求的签名是否一致
         */
        $signature = base64_encode(hash_hmac('sha256', $signString, $signSecret, true));
        logs()->info('api-signature-info', [
            'signStr' => $signString,
            'signStrHash' => sha1($signString),
            'signSecret' => $signSecret,
            'sign' => $signature,
        ]);
        if ($signature !== $request->getHeaderLine('X-Ca-Signature')) {
            throw new CustomException(trans('middleware.signature.signature_invalid'), 41201, 412, ['signStr' => $signString]);
        }
        return $handler->handle($request);
    }
}
