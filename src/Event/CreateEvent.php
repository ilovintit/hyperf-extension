<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Traits\HeaderToBag;
use Hyperf\Database\Model\Model;

abstract class CreateEvent extends ValidatorEvent
{
    use HeaderToBag;

    /**
     * @var Model
     */

    public $newModel;

    /**
     * CreateEvent constructor.
     * @param $input
     * @param array $headers
     */

    public function __construct($input, $headers = [])
    {
        $this->headerToBag($headers);
        $this->validateInput($input);
        $modelClass = $this->modelClass();
        $this->newModel = new $modelClass();
    }

    /**
     * @return string
     */

    abstract protected function modelClass();

    /**
     * @return string
     */

    public function successMessage()
    {
        return '保存成功';
    }

    /**
     * @return string
     */

    public function failedMessage()
    {
        return '保存失败';
    }
}
