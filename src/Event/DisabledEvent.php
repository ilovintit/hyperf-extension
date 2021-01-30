<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

abstract class DisabledEvent extends StatusEvent
{

    /**
     * @return string
     */
    public function successMessage()
    {
        return '禁用成功';
    }

    /**
     * @return string
     */
    public function failedMessage()
    {
        return '禁用失败';
    }
}
