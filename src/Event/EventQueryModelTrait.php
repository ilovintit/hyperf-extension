<?php
declare(strict_types=1);

namespace Iit\HyLib\Contracts;

use Iit\HyLib\Exceptions\CustomException;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;

trait EventQueryModelTrait
{
    /**
     * @var Model
     */

    public $currentModel;

    /**
     * @var string
     */

    public $code;

    /**
     * @param $code
     */

    public function query($code)
    {
        $this->code = $code;
        if (!$this->currentModel = $this->queryModel()->where($this->queryPrimary(), $code)->first()) {
            throw new CustomException($this->notFoundMessage());
        }
    }

    /**
     * @return Builder
     */

    abstract protected function queryModel();

    /**
     * @return string
     */

    protected function queryPrimary()
    {
        return 'code';
    }

    /**
     * @return string
     */

    public function notFoundMessage()
    {
        return '不存在的模型';
    }
}
