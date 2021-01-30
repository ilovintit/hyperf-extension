<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Exceptions\CustomException;
use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;

abstract class AbstractWebservice
{
    /**
     * @var string
     */

    public $serverProtocol = 'http://';

    /**
     * @var string
     */

    public $serverAddress;

    /**
     * @var int
     */

    public $serverPort;

    /**
     * @var array
     */

    public $config;

    /**
     * @var string
     */

    public $messageMark = 'message';

    /**
     * @var string
     */

    public $returnMark = 'return';

    /**
     * @var string
     */

    public $errorMessageMark = 'error';

    /**
     * @var
     */

    protected $_lastErrorMessage;

    /**
     * @var Client
     */
    protected $client;

    /**
     * AbstractWebservice constructor.
     * @param ClientFactory $clientFactory
     * @param array $config
     */

    public function __construct(ClientFactory $clientFactory, array $config)
    {
        $this->config = $config;
        $this->serverProtocol = isset($config['serverProtocol']) && !empty($config['serverProtocol']) ? $config['serverProtocol'] : 'http://';
        $this->serverAddress = isset($config['serverAddress']) && !empty($config['serverAddress']) ? $config['serverAddress'] : '';
        $this->serverPort = isset($config['serverPort']) && !empty($config['serverPort']) ? $config['serverPort'] : '';
        $this->messageMark = isset($config['messageMark']) && !empty($config['messageMark']) ? $config['messageMark'] : 'message';
        $this->returnMark = isset($config['returnMark']) && !empty($config['returnMark']) ? $config['returnMark'] : 'return';
        $this->errorMessageMark = isset($config['errorMessageMark']) && !empty($config['errorMessageMark']) ? $config['errorMessageMark'] : 'error';
        if (empty($this->serverAddress)) {
            throw new CustomException('webservice地址不能为空');
        }
        $config = ['base_uri' => $this->serverProtocol . $this->serverAddress . ':' . $this->serverPort, 'timeout' => 3];
        logs()->info('webservice-client-config', $config);
        $config = isset($config['client']) && is_array($config['client']) ? array_merge($config, $config['client']) : $config;
        $this->client = $clientFactory->create($config);
    }

    /**
     * @param $path
     * @param $xml
     * @return bool
     */

    public function request($path, $xml)
    {
        logs()->info('request-webservice', ['path' => $path, 'xml' => $xml]);
        try {
            $response = $this->client->post($path, [
                'body' => $xml,
                'headers' => [
                    'Content-Type' => 'text/xml',
                ],
                'http_errors' => false,
            ]);
            $content = $response->getBody()->getContents();
            logs()->info('request-webservice-result', ['content' => $content, 'status' => $response->getStatusCode()]);
            if ($response->getStatusCode() !== 200) {
                $this->_lastErrorMessage = empty($this->decodeErrorMessage($content)) ? $this->decodeMessage($content) : $this->decodeErrorMessage($content);
                return false;
            }
            return $this->decodeReturn($content);
        } catch (Exception $exception) {
            logs()->error('request-webservice-exception', ['exception' => $exception->__toString()]);
            return false;
        }
    }

    /**
     * @param $body
     * @param $beginHtml
     * @param $endHtml
     * @return bool|false|string
     */

    public function decodeResult($body, $beginHtml, $endHtml)
    {
        if (!strpos($body, $beginHtml)) {
            return false;
        }
        $beginPos = strpos($body, $beginHtml) + strlen($beginHtml);
        $endPos = strpos($body, $endHtml);
        return substr($body, $beginPos, $endPos - $beginPos);
    }

    /**
     * @param $body
     * @return bool|false|string
     */

    public function decodeReturn($body)
    {
        return $this->decodeResult($body, '<' . $this->returnMark . '>', '</' . $this->returnMark . '>');
    }

    /**
     * @param $body
     * @return bool|false|string
     */

    public function decodeMessage($body)
    {
        return $this->decodeResult($body, '<' . $this->messageMark . '>', '</' . $this->messageMark . '>');
    }

    /**
     * @param $body
     * @return bool|false|string
     */

    public function decodeErrorMessage($body)
    {
        return $this->decodeResult($body, '<' . $this->errorMessageMark . '>', '</' . $this->errorMessageMark . '>');
    }

    /**
     * @return string
     */

    public function getErrorMessage()
    {
        return $this->_lastErrorMessage;
    }
}
