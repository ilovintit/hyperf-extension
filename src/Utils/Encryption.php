<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

/**
 * Class Encryption
 * @package Iit\HyLib\Utils
 */
class Encryption
{

    /**
     * @var string
     */

    protected static string $cipher = 'aes-128-cbc';

    /**
     * @param $value
     * @return string
     */

    public static function encode($value): string
    {
        $iv = bin2hex(openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher)));
        $encodeValue = empty($value) ? null : openssl_encrypt($value, self::$cipher, self::key(), OPENSSL_ZERO_PADDING, hex2bin($iv));
        return empty($encodeValue) ? '' : base64_encode(json_encode(['value' => $encodeValue, 'iv' => $iv]));
    }

    /**
     * @param $value
     * @return string
     */

    public static function decode($value): string
    {
        $decodeValue = json_decode(base64_decode($value), true);
        $decodeString = empty($decodeValue) ? null : openssl_decrypt($decodeValue['value'], self::$cipher, self::key(), OPENSSL_ZERO_PADDING, hex2bin($decodeValue['iv']));
        return rtrim($decodeString);
    }

    /**
     * @return string
     */

    protected static function key(): string
    {
        return hex2bin(config('tools.aes_secret_key'));
    }

}
