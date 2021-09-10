<?php
declare(strict_types=1);

namespace Iit\HyLib\Process;

trait ProcessControlHelper
{
    /**
     * @var int 循环指定次数后退出进程重启,释放内存
     */
    public int $maxHandleTips = 1000;

    /**
     * @var int 每次循环检测当前进程占用内存情况,超出则退出进程重启,释放内存,单位MB
     */
    public int $limitMemory = 1024;

    /**
     * @var int 已经循环的次数
     */
    protected int $hasHandleTips = 0;

    /**
     * @var int 已使用内存数量
     */
    protected int $usageMemory = 0;

    /**
     * 检查是否超出处理次数
     * @return bool
     */
    public function checkHandleTips(): bool
    {
        return $this->hasHandleTips >= $this->maxHandleTips;
    }

    /**
     * 增加已处理次数
     * @param int $tips
     */
    public function addHandleTips(int $tips = 1)
    {
        $this->hasHandleTips += $tips;
    }

    /**
     * 检查是否超出内存限制
     * @return bool
     */
    public function checkMemoryLimit(): bool
    {
        $this->usageMemory = memory_get_usage();
        return $this->usageMemory >= $this->limitMemory * 1024 * 1024;
    }
}
