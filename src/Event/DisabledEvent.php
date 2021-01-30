<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

/**
 * Class DisabledEvent
 * @package Iit\HyLib\Contracts
 */
abstract class DisabledEvent extends StatusEvent
{

    /**
     * @return string
     */
    public function successMessage(): string
    {
        return '禁用成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '禁用失败';
    }
}
