<?php
declare(strict_types=1);

namespace App\Extension\Utils;

use App\Extension\Filesystem\OssAdapter;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Utils\ApplicationContext;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;

class File
{
    /**
     * @param string $adapterName
     * @return Filesystem
     */

    public static function create($adapterName = 'default')
    {
        return ApplicationContext::getContainer()->get(FilesystemFactory::class)->get($adapterName);
    }

    /**
     * @return Filesystem
     */

    public static function oss()
    {
        return self::create('oss');
    }

    /**
     * @return AdapterInterface|OssAdapter
     */

    public static function ossAdapter()
    {
        return self::oss()->getAdapter();
    }
}
