<?php
declare(strict_types=1);

namespace Iit\HyLib\Filesystem;

use Carbon\Carbon;
use Exception;
use Iit\HyLib\Utils\H;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\Http\ResponseCore;
use OSS\OssClient;
use Xxtime\Flysystem\Aliyun\Supports;

class OssAdapter extends AbstractAdapter
{
    /**
     * @var Supports
     */
    public Supports $supports;

    /**
     * @var OssClient
     */
    private OssClient $oss;

    /**
     * @var string AliYun bucket
     */
    private $bucket;

    /**
     * @var string
     */
    private $endpoint = 'oss-cn-hangzhou.aliyuncs.com';

    /**
     * @var array
     */
    protected array $config;

    /**
     * OssAdapter constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        $isCName = false;
        $token = null;
        $this->supports = new Supports();
        try {
            $this->bucket = $config['bucket'];
            empty($config['endpoint']) ? null : $this->endpoint = $config['endpoint'];
            empty($config['timeout']) ? $config['timeout'] = 3600 : null;
            empty($config['connect_timeout']) ? $config['connect_timeout'] = 10 : null;

            if (!empty($config['isCName'])) {
                $this->endpoint = $config['cname'];
                $isCName = true;
            }
            if (!empty($config['token'])) {
                $token = $config['token'];
            }
            $this->oss = new OssClient(
                $config['access_id'], $config['access_secret'], $this->endpoint, $isCName, $token
            );
            $this->oss->setTimeout($config['timeout']);
            $this->oss->setConnectTimeout($config['connect_timeout']);
            $this->config = $config;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false|null false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        $result = $this->oss->putObject($this->bucket, $path, $contents, $this->getOssOptions($config));
        $this->supports->setFlashData($result);
        return true;
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     * @throws OssException
     */
    public function writeStream($path, $resource, Config $config)
    {
        if (!is_resource($resource)) {
            return false;
        }
        $i = 0;
        $bufferSize = 1000000; // 1M
        while (!feof($resource)) {
            if (false === $buffer = fread($resource, $block = $bufferSize)) {
                return false;
            }
            $position = $i * $bufferSize;
            $size = $this->oss->appendObject($this->bucket, $path, $buffer, $position, $this->getOssOptions($config));
            $i++;
        }
        fclose($resource);
        return true;
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        $result = $this->oss->putObject($this->bucket, $path, $contents, $this->getOssOptions($config));
        $this->supports->setFlashData($result);
        return true;
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        $result = $this->write($path, stream_get_contents($resource), $config);
        if (is_resource($resource)) {
            fclose($resource);
        }
        return $result;
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     * @throws OssException
     */
    public function rename($path, $newpath)
    {
        $this->oss->copyObject($this->bucket, $path, $this->bucket, $newpath);
        $this->oss->deleteObject($this->bucket, $path);
        return true;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     * @throws OssException
     */
    public function copy($path, $newpath)
    {
        $this->oss->copyObject($this->bucket, $path, $this->bucket, $newpath);
        return true;
    }

    /**
     * BatchDelete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $this->oss->deleteObject($this->bucket, $path);
        return true;
    }

    /**
     * BatchDelete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        $lists = $this->listContents($dirname, true);
        if (!$lists) {
            return false;
        }
        $objectList = [];
        foreach ($lists as $value) {
            $objectList[] = $value['path'];
        }
        $this->oss->deleteObjects($this->bucket, $objectList);
        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $this->oss->createObjectDir($this->bucket, $dirname);
        return true;
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     *
     * Aliyun OSS ACL value: 'default', 'private', 'public-read', 'public-read-write'
     * @throws OssException
     */
    public function setVisibility($path, $visibility)
    {
        $this->oss->putObjectAcl(
            $this->bucket,
            $path,
            ($visibility == 'public') ? 'public-read' : 'private'
        );
        return true;
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        return $this->oss->doesObjectExist($this->bucket, $path);
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        return [
            'contents' => $this->oss->getObject($this->bucket, $path)
        ];
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $resource = 'http://' . $this->bucket . '.' . $this->endpoint . '/' . $path;
        return [
            'stream' => $resource = fopen($resource, 'r')
        ];
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     * @throws OssException
     */
    public function listContents($directory = '', $recursive = false)
    {
        $directory = rtrim($directory, '\\/');

        $result = [];
        $nextMarker = '';
        while (true) {
            // max-keys 用于限定此次返回object的最大数，如果不设定，默认为100，max-keys取值不能大于1000。
            // prefix   限定返回的object key必须以prefix作为前缀。注意使用prefix查询时，返回的key中仍会包含prefix。
            // delimiter是一个用于对Object名字进行分组的字符。所有名字包含指定的前缀且第一次出现delimiter字符之间的object作为一组元素
            // marker   用户设定结果从marker之后按字母排序的第一个开始返回。
            $options = [
                'max-keys' => 1000,
                'prefix' => $directory . '/',
                'delimiter' => '/',
                'marker' => $nextMarker,
            ];
            $res = $this->oss->listObjects($this->bucket, $options);

            // 得到nextMarker，从上一次$res读到的最后一个文件的下一个文件开始继续获取文件列表
            $nextMarker = $res->getNextMarker();
            $prefixList = $res->getPrefixList(); // 目录列表
            $objectList = $res->getObjectList(); // 文件列表
            if ($prefixList) {
                foreach ($prefixList as $value) {
                    $result[] = [
                        'type' => 'dir',
                        'path' => $value->getPrefix()
                    ];
                    if ($recursive) {
                        $result = array_merge($result, $this->listContents($value->getPrefix(), $recursive));
                    }
                }
            }
            if ($objectList) {
                foreach ($objectList as $value) {
                    if (($value->getSize() === 0) && ($value->getKey() === $directory . '/')) {
                        continue;
                    }
                    $result[] = [
                        'type' => 'file',
                        'path' => $value->getKey(),
                        'timestamp' => strtotime($value->getLastModified()),
                        'size' => $value->getSize()
                    ];
                }
            }
            if ($nextMarker === '') {
                break;
            }
        }

        return $result;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        return $this->oss->getObjectMeta($this->bucket, $path);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        $response = $this->oss->getObjectMeta($this->bucket, $path);
        return [
            'size' => $response['content-length']
        ];
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $response = $this->oss->getObjectMeta($this->bucket, $path);
        return [
            'mimetype' => $response['content-type']
        ];
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        $response = $this->oss->getObjectMeta($this->bucket, $path);
        return [
            'timestamp' => $response['last-modified']
        ];
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     * @throws OssException
     */
    public function getVisibility($path)
    {
        $response = $this->oss->getObjectAcl($this->bucket, $path);
        return [
            'visibility' => $response,
        ];
    }

    /**
     * Get OSS Options
     * @param Config $config
     * @return array
     */
    private function getOssOptions(Config $config)
    {
        $options = [];
        if ($config->has("headers")) {
            $options['headers'] = $config->get("headers");
        }

        if ($config->has("Content-Type")) {
            $options["Content-Type"] = $config->get("Content-Type");
        }

        if ($config->has("Content-Md5")) {
            $options["Content-Md5"] = $config->get("Content-Md5");
            $options["checkmd5"] = false;
        }
        return $options;
    }

    /**
     * @return OssClient
     */

    public function getOssClient()
    {
        return $this->oss;
    }

    /**
     * @param $object
     * @param null $prefix
     * @return ResponseCore|string
     * @throws OssException
     */

    public function getUploadUrl($object, $prefix = null)
    {
        $this->oss->setUseSSL(true);
        return $this->oss->signUrl($this->bucket, $prefix . $object, 900, OssClient::OSS_HTTP_PUT);
    }

    /**
     * @param $object
     * @param bool $isPublic
     * @param null $prefix
     * @return ResponseCore|string
     */

    public function getObjectUrl($object, $prefix = null, $isPublic = false)
    {
        try {
            if (empty($object)) {
                return null;
            }
            $isCname = empty($this->config['cname']) ? false : true;
            $token = !empty($this->config['token']) ? $this->config['token'] : null;
            $endpoint = $isCname ? $this->config['cname'] : $this->endpoint;
            $this->oss = new OssClient(
                $this->config['access_id'], $this->config['access_secret'], $endpoint, $isCname, $token
            );
            if (!$isPublic) {
                $this->oss->setUseSSL(true);
                return str_replace('http://', 'https://', $this->oss->signUrl($this->bucket, $prefix . $object));
            }
            $isCname = empty($this->config['cname']) ? false : true;
            $endpoint = $this->config['cname'];
            $ret_endpoint = null;
            if (strpos($endpoint, 'http://') === 0) {
                $ret_endpoint = substr($endpoint, strlen('http://'));
            } elseif (strpos($endpoint, 'https://') === 0) {
                $ret_endpoint = substr($endpoint, strlen('https://'));
            } else {
                $ret_endpoint = $endpoint;
            }
            $hostname = OssUtil::getHostPortFromEndpoint($ret_endpoint);
            if (!$isCname) {
                $hostname = ($this->bucket == '') ? $hostname : ($this->bucket . '.') . $hostname;
            }
            return 'https://' . $hostname . '/' . $prefix . $object;
        } catch (OssException $exception) {
            return null;
        }
    }

    /**
     * @param null $filename
     * @param null $prefix
     * @param int $expired
     * @return array
     */

    public function generatePostObjectInfo($filename = null, $prefix = null, $expired = 5): array
    {
        $filename = $filename === null ? sha1(strval(H::uuid())) : $filename;
        $policy = base64_encode(json_encode([
            'expiration' => Carbon::now()->addMinutes($expired)->format('Y-m-d\TH:i:s\Z'),
            "conditions" => [['key' => $prefix . $filename]]
        ]));
        return [
            'postUrl' => 'https://' . (isset($this->config['cname']) ? $this->config['cname'] : $this->bucket . '.' . $this->endpoint),
            'filename' => $filename,
            'key' => $prefix . $filename,
            'OSSAccessKeyId' => $this->config['access_id'],
            'policy' => $policy,
            'signature' => base64_encode(hash_hmac('sha1', $policy, $this->config['access_secret'], true)),
        ];
    }
}
