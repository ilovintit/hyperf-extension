<?php
declare(strict_types=1);

namespace App\Extension\Contracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

abstract class AbstractApiService
{
    /**
     * @var string
     */
    public $appId;

    /**
     * @var string
     */
    public $appSecret;

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var CacheInterface
     */
    public $cache;

    /**
     * AbstractApiService constructor.
     * @param CacheInterface $cache
     * @param ClientFactory $clientFactory
     */
    public function __construct(CacheInterface $cache, ClientFactory $clientFactory)
    {
        $this->cache = $cache;
        $this->client = $clientFactory->create([]);
    }

    /**
     * @param $url
     * @return $this
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $params
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function sendRequest($method, $uri, array $params = [])
    {
        return $this->client->request($method, $this->baseUrl . $uri, $this->signRequest($method, $uri, $params));
    }

    /**
     * @param $method
     * @param $uri
     * @param array $params
     * @return array
     */
    public function signRequest($method, $uri, array $params)
    {
        $accept = '';
        $bodyMd5 = '';
        $contentType = '';
        $date = '';
        $params['headers'] = isset($params['headers']) ? $params['headers'] : [];
        $params['headers']['X-Ca-Version'] = 1;
        $params['headers']['X-Ca-Request-Mode'] = 'debug';
        $params['headers']['X-Ca-Stage'] = 'RELEASE';
        $params['headers']['X-Ca-Timestamp'] = strval(time() * 1000);
        $params['headers']['X-Ca-Nonce'] = uuid();
        $signHeaders = $params['headers'];
        ksort($signHeaders);
        $templateHeaders = [];
        foreach ($signHeaders as $key => $header) {
            $templateHeaders[] = $key . ':' . $header;
        }
        $signQueryString = '';
        $signQueryList = [];
        $queryList = isset($params['query']) ? $params['query'] : [];
        ksort($queryList);
        foreach ($queryList as $key => $query) {
            $signQueryList[] = $key . '=' . $query;
        }
        if (!empty($signQueryList)) {
            $signQueryString = '?' . implode('&', $signQueryList);
        }
        $signString = strtoupper($method) . "\n"
            . $accept . "\n"
            . $bodyMd5 . "\n"
            . $contentType . "\n"
            . $date . "\n"
            . implode("\n", $templateHeaders) . "\n"
            . $uri . $signQueryString;
        $params['headers']['X-Ca-Key'] = $this->appId;
        $params['headers']['X-Ca-Signature-Method'] = 'HmacSHA256';
        $params['headers']['X-Ca-Signature-Headers'] = implode(',', array_keys($signHeaders));
        $params['headers']['X-Ca-Signature'] = base64_encode(hash_hmac('sha256', $signString, $this->appSecret, true));
        $params['http_errors'] = false;
        $params['query'] = $queryList;
        return $params;
    }
}
