<?php
declare(strict_types=1);

namespace App\Utils;

use ArrayAccess;
use ArrayObject;
use Hyperf\Utils\Arr;

/**
 * Class Optional
 * @package App\Utils
 */
class Optional implements ArrayAccess
{

    /**
     * The underlying object.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Create a new optional instance.
     *
     * @param mixed $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Dynamically access a property on the underlying object.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        if (is_object($this->value)) {
            return $this->value->{$key} ?? null;
        }
    }

    /**
     * Dynamically check a property exists on the underlying object.
     *
     * @param mixed $name
     * @return bool
     */
    public function __isset($name): bool
    {
        if (is_object($this->value)) {
            return isset($this->value->{$name});
        }

        if (is_array($this->value) || $this->value instanceof ArrayObject) {
            return isset($this->value[$name]);
        }

        return false;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return Arr::accessible($this->value) && Arr::exists($this->value, $offset);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return Arr::get($this->value, $offset);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (Arr::accessible($this->value)) {
            $this->value[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (Arr::accessible($this->value)) {
            unset($this->value[$offset]);
        }
    }

    /**
     * Dynamically pass a method to the underlying object.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (is_object($this->value)) {
            return $this->value->{$method}(...$parameters);
        }
    }

    /**
     * @param null $value
     * @param callable|null $callback
     * @return Optional
     */
    public static function create($value = null, callable $callback = null): Optional
    {
        if (is_null($callback)) {
            return new self($value);
        } elseif (!is_null($value)) {
            return new self($callback($value));
        }
    }
}
