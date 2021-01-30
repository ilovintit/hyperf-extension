<?php
declare(strict_types=1);

namespace Iit\HyLib\CastsAttributes;

use Hyperf\Contract\CastsAttributes;

/**
 * Class ArraySearch
 * @package Iit\HyLib\CastsAttributes
 */
class ArraySearch implements CastsAttributes
{

    /**
     * Transform the attribute from the underlying model values.
     *
     * @param $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function get($model, string $key, $value, array $attributes): array
    {
        return empty($value) ? [] : explode('|', substr($value, 1, strlen($value) - 2));
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        return empty($value) ? '' : '|' . implode('|', $value) . '|';
    }
}
