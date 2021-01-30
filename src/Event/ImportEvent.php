<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

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
    public function successMessage(): string
    {
        return '导入成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '导入失败';
    }
}
