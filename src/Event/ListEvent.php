<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

/**
 * Class ListEvent
 * @package Iit\HyLib\Contracts
 */
abstract class ListEvent extends AbstractEvent
{
    use HeaderToBag;

    /**
     * ListEvent constructor.
     * @param array $headers
     */
    public function __construct(array $headers)
    {
        $this->headerToBag($headers);
    }

    /**
     * @return string
     */
    public function successMessage(): string
    {
        return '查询成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '查询失败';
    }
}
