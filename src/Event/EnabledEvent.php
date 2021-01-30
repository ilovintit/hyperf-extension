<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

/**
 * Class EnabledEvent
 * @package App\Extend
 */
abstract class EnabledEvent extends StatusEvent
{

    /**
     * @return string
     */
    public function successMessage(): string
    {
        return '启用成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '启用失败';
    }
}
