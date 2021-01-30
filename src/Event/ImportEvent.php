<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

abstract class ImportEvent extends ValidatorEvent
{
    /**
     * ImportEvent constructor.
     * @param $input
     */
    public function __construct($input)
    {
        $this->validateInput($input);
    }

    /**
     * @return string
     */

    public function successMessage()
    {
        return '导入成功';
    }

    /**
     * @return string
     */

    public function failedMessage()
    {
        return '导入失败';
    }
}
