<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Iit\HyLib\Auth\Annotation;

use Iit\HyLib\Auth\Config;
use Iit\HyLib\Auth\Contract\UserProvider;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * Class UserProviderAnnotation.
 *
 * @Annotation
 * @Target("CLASS")
 */
class UserProviderAnnotation extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $value;

    public function __construct($value = null)
    {
        parent::__construct($value);
    }

    public function collectClass(string $className): void
    {
        parent::collectClass($className);
        Config::setAnnotation($this->value, $className, UserProvider::class);
    }
}
