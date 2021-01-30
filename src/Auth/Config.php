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

namespace Iit\HyLib\Auth;

use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * Class Config.
 */
class Config
{
    const COLLECTOR_KEY = 'httpAuth';

    /**
     * @param string $name
     * @param string $value
     * @param string $abstract
     */
    public static function setAnnotation($name, $value, $abstract)
    {
        $httpAuth = AnnotationCollector::get(self::COLLECTOR_KEY);
        $httpAuth[$abstract][$name] = $value;
        AnnotationCollector::set(self::COLLECTOR_KEY, $httpAuth);
    }

    /**
     * @param $name
     * @param $abstract
     * @return string
     */
    public static function getAnnotation($name, $abstract)
    {
        $httpAuth = AnnotationCollector::get(self::COLLECTOR_KEY);
        return $httpAuth[$abstract][$name] ?? '';
    }
}
