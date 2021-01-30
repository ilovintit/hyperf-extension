<?php
declare(strict_types=1);

namespace App\Extension\Contracts;

use App\Extension\Exceptions\CustomException;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use Psr\SimpleCache\CacheInterface;

abstract class AliYunApiService extends AbstractApiService
{
    /**
     * @return string
     */
    abstract protected function baseUrl();

    /**
     * @return string
     */
    abstract protected function appId();

    /**
     * @return string
     */
    abstract protected function appSecret();

    /**
     * AliYunApiService constructor.
     * @param CacheInterface $cache
     * @param ClientFactory $clientFactory
     */
    public function __construct(CacheInterface $cache, ClientFactory $clientFactory)
    {
        parent::__construct($cache, $clientFactory);
        $this->setBaseUrl($this->baseUrl());
        $this->appId = $this->appId();
        $this->appSecret = $this->appSecret();
    }

    /**
     * @param $method
     * @param $uri
     * @param array $params
     * @return string
     */
    public function request($method, $uri, array $params = [])
    {
        try {
            logs()->info('try-request-ali-yun-api-service', ['method' => $method, 'uri' => $uri, 'params' => $params]);
            $response = $this->sendRequest($method, $uri, $params);
            $responseContents = $response->getBody()->getContents();
            logs()->info('ali-yun-api-service-response', [
                'headers' => $response->getHeaders(),
                'status' => $response->getStatusCode(),
                'content' => $responseContents,
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new CustomException($response->getHeader('X-Ca-Error-Message')[0]);
            }
            return $responseContents;
        } catch (Exception $exception) {
            throw new CustomException($exception->getMessage());
        } catch (GuzzleException $exception) {
            throw new CustomException($exception->getMessage());
        }
    }

}
