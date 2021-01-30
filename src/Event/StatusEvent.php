<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

abstract class StatusEvent extends UpdateEvent
{
    /**
     * @return string
     */

    public function successMessage()
    {
        return '修改状态成功';
    }

    /**
     * @return string
     */

    public function failedMessage()
    {
        return '修改状态失败';
    }
}
