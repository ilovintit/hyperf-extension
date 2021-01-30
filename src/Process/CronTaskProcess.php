<?php
declare(strict_types=1);

namespace Iit\HyLib\Process;

use Hyperf\Process\AbstractProcess;
use Iit\HyLib\RedisLock\RedisLock;
use Exception;
use Iit\HyLib\Utils\Log;

/**
 * Class CronTaskProcess
 * @package Iit\HyLib\Process
 */
abstract class CronTaskProcess extends AbstractProcess
{
    /**
     * @var RedisLock
     */
    public RedisLock $lock;

    /**
     * The logical of process will place in here.
     */
    public function handle(): void
    {
        $this->logInfo('start-process-at:' . microtime(true));
        $lockTime = $this->runInterval();
        $this->logInfo('run-interval-is:' . $lockTime);
        if ($lockTime <= 0) {
            $this->logInfo('run-interval-invalid,after-3600-second-exit:' . microtime(true));
            sleep(3600);
            exit;
        }
        $this->lock = RedisLock::create($this->taskKey(), $lockTime);
        do {
            $this->logInfo('try-to-lock-key:' . microtime(true));
            if (!$this->lock->get()) {
                $this->logInfo('get-lock-failed,sleep:' . microtime(true));
                sleep($this->sleepTime());
                continue;
            }
            $this->logInfo('get-lock-successful:' . microtime(true));
            try {
                $taskResult = $this->cronTask();
                if ($taskResult === 'delay') {
                    sleep($this->sleepTime());
                }
                $this->lock->release();
            } catch (Exception $exception) {
                Log::error($exception->__toString());
                $this->logInfo('run-cron-task-exception:' . microtime(true));
                sleep($this->sleepTime());
                $this->lock->release();
            }
        } while (true);
    }

    /**
     * @return int
     */
    protected function sleepTime(): int
    {
        return intval(round(rand($this->runInterval() * 100 / 2, $this->runInterval() * 100) / 100, 0));
    }

    /**
     * @param $message
     * @param array $content
     */
    protected function logInfo($message, $content = [])
    {
        Log::info($this->taskKey() . ':' . $message, $content);
    }

    /**
     * @return string
     */
    public function taskKey(): string
    {
        return str_replace('_', '-', snake_case((new \ReflectionClass(static::class))->getShortName()));
    }

    /**
     * 间隔运行时间,单位秒
     * @return integer
     */
    abstract public function runInterval(): int;

    /**
     * 执行任务
     */
    abstract public function cronTask();
}
