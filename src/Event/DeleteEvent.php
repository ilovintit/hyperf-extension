<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Traits\HeaderToBag;

/**
 * Class DeleteEvent
 * @package Iit\HyLib\Contracts
 */
abstract class DeleteEvent extends AbstractEvent
{
    use EventQueryModelTrait, HeaderToBag;

    /**
     * DeleteEvent constructor.
     * @param $code
     * @param array $headers
     */
    public function __construct($code, $headers = [])
    {
        $this->headerToBag($headers);
        $this->query($code);
    }

    /**
     * @return string
     */
    public function successMessage(): string
    {
        return '删除成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '删除失败';
    }
}
