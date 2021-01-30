<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Traits\HeaderToBag;

abstract class UpdateEvent extends ValidatorEvent
{
    use EventQueryModelTrait, HeaderToBag;

    /**
     * UpdateEvent constructor.
     * @param $code
     * @param array $input
     * @param array $headers
     */
    public function __construct($code, array $input, $headers = [])
    {
        $this->query($code);
        $this->headerToBag($headers);
        $this->validateInput($input);
    }

    /**
     * @return string
     */
    public function successMessage(): string
    {
        return '保存成功';
    }

    /**
     * @return string
     */
    public function failedMessage(): string
    {
        return '保存失败';
    }
}
