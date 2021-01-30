<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

/**
 * Class Str
 * @package Iit\HyLib\Utils
 */
class Str extends \Hyperf\Utils\Str
{
    /**
     * @param $string
     * @return int|string
     */
    public static function camelCase($string)
    {
        return is_numeric($string) ? $string : parent::camel($string);
    }

    /**
     * @param $string
     * @return int|string
     */
    public static function snakeCase($string)
    {
        return is_numeric($string) ? $string : parent::snake($string);
    }
}
