<?php
declare(strict_types=1);

namespace Iit\HyLib\Manager;

use Iit\HyLib\Utils\H;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class VerificationCodeManager
 * @package Iit\HyLib\Manager
 */
class VerificationCodeManager
{
    /**
     * @var string
     */
    private string $cachePrefix = 'verification_code:';

    /**
     * @var string
     */
    protected string $codeId;

    /**
     * @var string
     */
    protected string $recipient;

    /**
     * @var integer
     */
    protected int $code;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * VerificationCodeManager constructor.
     * @param CacheInterface $cache
     */
    function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return int
     */
    public function generateCode(): int
    {
        return rand(100000, 999999);
    }

    /**
     * @param $codeId
     * @return string
     */
    protected function getCacheKey($codeId): string
    {
        return $this->cachePrefix . $codeId;
    }

    /**
     * @param $codeId
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCodeId($codeId): VerificationCodeManager
    {
        if ($codeId !== $this->codeId) {
            $this->codeId = $codeId;
            $cacheInfo = $this->cache->get($this->getCacheKey($codeId));
            list($this->recipient, $this->code) = empty($cacheInfo) ? [null, null] : json_decode($cacheInfo, true);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param $recipient
     * @param $code
     * @param null $expiredTime
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function saveCode($recipient, $code, $expiredTime = null)
    {
        $expiredTime = $expiredTime === null ? config('tools.verification_code_expired_time', 180) : $expiredTime;
        $codeId = H::uuid();
        $this->cache->set($this->getCacheKey($codeId), json_encode([$recipient, $code]), $expiredTime);
        return $codeId;
    }

    /**
     * @param $recipient
     * @param null $expiredTime
     * @return array
     * @throws InvalidArgumentException
     */
    public function generateAndSave($recipient, $expiredTime = null): array
    {
        $code = $this->generateCode();
        $smsId = $this->saveCode($recipient, $code, $expiredTime);
        return [$smsId, $code];
    }

    /**
     * @param $codeId
     * @param $recipient
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateCodeId($codeId, $recipient): bool
    {
        return $this->setCodeId($codeId)->getRecipient() === $recipient;
    }

    /**
     * @param $codeId
     * @param $code
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateSaveCode($codeId, $code): bool
    {
        return $this->setCodeId($codeId)->getCode() === (int)$code;
    }

    /**
     * @param $codeId
     * @throws InvalidArgumentException
     */
    public function destroySaveCode($codeId)
    {
        $this->cache->delete($this->getCacheKey($codeId));
    }

    /**
     * @param $codeId
     * @param $code
     * @param bool $force
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateAndDestroySaveCode($codeId, $code, $force = false): bool
    {
        $validateResult = $this->validateSaveCode($codeId, $code);
        if ($validateResult === true || $force === true) {
            $this->destroySaveCode($codeId);
        }
        return $validateResult;
    }
}
