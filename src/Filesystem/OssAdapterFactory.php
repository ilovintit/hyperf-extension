<?php
declare(strict_types=1);

namespace Iit\HyLib\Filesystem;

use Exception;
use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use League\Flysystem\AdapterInterface;

class OssAdapterFactory implements AdapterFactoryInterface
{

    /**
     * @param array $options
     * @return AdapterInterface
     * @throws Exception
     */

    public function make(array $options): AdapterInterface
    {
        return new OssAdapter($options);
    }
}
