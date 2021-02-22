<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Iit\HyLib\Filesystem\OssAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;

class OssFile
{
    /**
     * @return Filesystem
     */
    public static function make(): Filesystem
    {
        return File::create('oss');
    }

    /**
     * @return AdapterInterface|OssAdapter
     */
    public static function adapter(): AdapterInterface
    {
        return self::make()->getAdapter();
    }
}
