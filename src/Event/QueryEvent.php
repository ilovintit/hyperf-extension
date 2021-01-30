<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Traits\HeaderToBag;

/**
 * Class QueryEvent
 * @package ZhiEq\Events
 */
abstract class QueryEvent extends AbstractEvent
{
    use EventQueryModelTrait, HeaderToBag;

    /**
     * QueryEvent constructor.
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
    public function successMessage()
    {
        return '查询成功';
    }

    /**
     * @return string
     */
    public function failedMessage()
    {
        return '查询失败';
    }
}
