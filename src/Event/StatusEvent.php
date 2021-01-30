<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

/**
 * Class StatusEvent
 * @package Iit\HyLib\Contracts
 */
abstract class StatusEvent extends UpdateEvent
{
    /**
     * @return string
     */
    public function successMessage(): string
    {
        return '修改状态成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '修改状态失败';
    }
}
