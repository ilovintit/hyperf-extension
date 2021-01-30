<?php
declare(strict_types=1);

namespace Iit\HyLib\Event;

use Iit\HyLib\Exceptions\CustomException;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;

/**
 * Trait EventQueryModelTrait
 * @package Iit\HyLib\Contracts
 */
trait EventQueryModelTrait
{
    /**
     * @var Model|null
     */
    public ?Model $currentModel;

    /**
     * @var string
     */
    public string $code;

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
    abstract protected function queryModel(): Builder;

    /**
     * @return string
     */
    protected function queryPrimary(): string
    {
        return 'code';
    }

    /**
     * @return string
     */
    public function notFoundMessage(): string
    {
        return '不存在的模型';
    }
}
