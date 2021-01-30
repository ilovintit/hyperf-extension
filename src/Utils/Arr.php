<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Utils\Collection;

/**
 * Class Arr
 * @package Iit\HyLib\Utils
 */
class Arr extends \Hyperf\Utils\Arr
{

    /**
     * @param array $filterData
     * @return array
     */
    public static function filterNull(array $filterData): array
    {
        foreach ($filterData as $key => &$item) {
            if (is_array($item)) {
                $item = self::filterNull($item);
            } else if ($item === null) {
                $item = '';
            }
        }
        return $filterData;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function toRealType(array $array): array
    {
        return array_map(function ($arrayValue) {
            if (is_array($arrayValue)) {
                return self::toRealType($arrayValue);
            }
            if (is_numeric($arrayValue)) {
                return is_integer($arrayValue) || is_float($arrayValue) ? floatval($arrayValue) : $arrayValue;
            }
            if (is_string($arrayValue) && empty($arrayValue)) {
                return null;
            }
            return $arrayValue;
        }, $array);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function camelCaseArrayKeys(array $array): array
    {
        $return = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $return[Str::camelCase($key)] = Arr::camelCaseArrayKeys($item);
            } else if ($item instanceof Collection) {
                $return[Str::camelCase($key)] = Arr::camelCaseArrayKeys($item->toArray());
            } else {
                $return[Str::camelCase($key)] = $item;
            }
        }
        return $return;
    }
}
