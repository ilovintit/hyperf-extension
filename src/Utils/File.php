<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Utils\ApplicationContext;
use League\Flysystem\Filesystem;

class File
{
    /**
     * @param string $adapterName
     * @return Filesystem
     */

    public static function create($adapterName = 'default'): Filesystem
    {
        return ApplicationContext::getContainer()->get(FilesystemFactory::class)->get($adapterName);
    }
}
