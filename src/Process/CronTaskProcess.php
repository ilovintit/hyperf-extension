<?php
declare(strict_types=1);

namespace Iit\HyLib\Process;

use Hyperf\Process\AbstractProcess;
use Iit\HyLib\RedisLock\RedisLock;
use Exception;
use Iit\HyLib\Utils\Log;
use Iit\HyLib\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CronTaskProcess
 * @package Iit\HyLib\Process
 * @method logEmergency($message, array $context = [])
 * @method logAlert($message, array $context = [])
 * @method logCritical($message, array $context = [])
 * @method logError($message, array $context = [])
 * @method logWarning($message, array $context = [])
 * @method logNotice($message, array $context = [])
 * @method logInfo($message, array $context = [])
 * @method  logDebug($message, array $context = [])
 */
abstract class CronTaskProcess extends AbstractProcess
{
    const SIGNAL_DELAY = 'delay';
    /**
     * @var RedisLock
     */
    public RedisLock $lock;

    /**
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * CronTaskProcess constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->logger = Log::driver();
        parent::__construct($container);
    }

    /**
     * The logical of process will place in here.
     */
    public function handle(): void
    {
        $this->logInfo('process-started');
        $lockTime = $this->runInterval();
        $this->logInfo('process-interval' . $lockTime);
        if ($lockTime <= 0) {
            $this->logInfo('process-interval-invalid,exit');
            sleep(3600);
            exit;
        }
        $this->logInfo('process-task-key:' . $this->taskKey());
        $this->lock = RedisLock::create($this->taskKey(), $lockTime);
        do {
            $this->logInfo('process-try-lock-key');
            if (!$this->lock->get()) {
                $this->logInfo('process-lock-failed,sleep');
                sleep($this->sleepTime());
                continue;
            }
            $this->logInfo('process-lock-successful');
            try {
                $this->cronTask() === self::SIGNAL_DELAY
                && sleep($this->sleepTime());
                $this->lock->release();
            } catch (Exception $exception) {
                $this->logError('process-task-exception', ['exception' => $exception->__toString()]);
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
     * 任务识别标识,默认使用类名转换,子类可以覆盖此方法自定义标识
     * @return string
     */
    public function taskKey(): string
    {
        return str_replace('_', '-', Str::snakeCase((new \ReflectionClass(static::class))->getShortName()));
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        if (Str::start($name, 'log')) {
            $msg = $this->taskKey() . '-' . $arguments[0] . '/' . microtime(true);
            $cxt = isset($arguments[1]) ? (is_array($arguments[1]) ? $arguments[1] : []) : [];
            $this->logger->{str_replace('log', '', $name)}($msg, $cxt);
        }
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
