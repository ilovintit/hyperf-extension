<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

/**
 * Class Encryption
 * @package Iit\HyLib\Utils
 */
class EAD
{

    /**
     * @var string
     */
    public string $cipher = 'AES-128-CBC';
    /**
     * @var string
     */
    public string $key;

    /**
     * @var string
     */
    public string $iv;

    /**
     * @var int
     */
    public int $option = OPENSSL_RAW_DATA;

    /**
     * Encryption constructor.
     * @param string|null $configName
     */
    public function __construct(string $configName = null)
    {
        $configName = $configName === null ? 'basic' : $configName;
        $configList = config('library.ead.secrets');
        $config = $configList[$configName] ?? [];
        isset($config['option']) && !empty($config['option']) && $this->setOption($config['option']);
        isset($config['cipher']) && !empty($config['cipher']) && $this->setCipher($config['cipher']);
        isset($config['key']) && !empty($config['key']) && $this->setKey($config['key']);
        isset($config['iv']) && !empty($config['iv']) && $this->setIv($config['iv']);
    }

    /**
     * @param string $iv
     * @return EAD
     */
    public function setIv(string $iv): self
    {
        $this->iv = $iv;
        return $this;
    }

    /**
     * @param string $key
     * @return EAD
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param string $cipher
     * @return EAD
     */
    public function setCipher(string $cipher): self
    {
        $this->cipher = $cipher;
        return $this;
    }

    /**
     * @param int $option
     * @return EAD
     */
    public function setOption(int $option): self
    {
        $this->option = $option;
        return $this;
    }

    /**
     * @param $value
     * @param null $key
     * @param null $iv
     * @return string
     */
    public function encrypt($value, $key = null, $iv = null): string
    {
        $key = $key === null ? $this->key : $key;
        $iv = $iv === null ? $this->iv : $iv;
        return base64_encode(openssl_encrypt($value, $this->cipher, $key, $this->option, $iv));
    }

    /**
     * @param $value
     * @param null $key
     * @param null $iv
     * @return false|string
     */
    public function decrypt($value, $key = null, $iv = null): string
    {
        $key = $key === null ? $this->key : $key;
        $iv = $iv === null ? $this->iv : $iv;
        return openssl_decrypt(base64_decode($value), $this->cipher, $key, $this->option, $iv);
    }

    /**
     * @param $value
     * @param null $key
     * @return string
     */

    public function encode($value, $key = null): string
    {
        $key = $key === null ? $this->key : $key;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encodeValue = empty($value) ? null : openssl_encrypt($value, $this->cipher, hex2bin($key), OPENSSL_ZERO_PADDING, hex2bin($iv));
        return empty($encodeValue) ? '' : base64_encode(json_encode(['value' => $encodeValue, 'iv' => $iv]));
    }

    /**
     * @param $value
     * @param null $key
     * @return string
     */

    public function decode($value, $key = null): string
    {
        $key = $key === null ? $this->key : $key;
        $decodeValue = json_decode(base64_decode($value), true);
        $decodeString = empty($decodeValue) ? null : openssl_decrypt($decodeValue['value'], $this->cipher, hex2bin($key), OPENSSL_ZERO_PADDING, hex2bin($decodeValue['iv']));
        return rtrim($decodeString);
    }

}
