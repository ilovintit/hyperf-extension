<?php
declare(strict_types=1);

namespace App\Extension\Utils;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class VerificationCodeManager
{
    /**
     * @var string
     */

    private $cachePrefix = 'verification_code:';

    /**
     * @var string
     */

    protected $codeId;

    /**
     * @var string
     */

    protected $recipient;
    /**
     * @var integer
     */

    protected $code;

    /**
     * @var CacheInterface
     */

    private $cache;

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

    public function generateCode()
    {
        return rand(100000, 999999);
    }

    /**
     * @param $codeId
     * @return string
     */

    protected function getCacheKey($codeId)
    {
        return $this->cachePrefix . $codeId;
    }

    /**
     * @param $codeId
     * @return $this
     * @throws InvalidArgumentException
     */

    public function setCodeId($codeId)
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

    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return int
     */

    public function getCode()
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
        $codeId = uuid();
        $this->cache->set($this->getCacheKey($codeId), json_encode([$recipient, $code]), $expiredTime);
        return $codeId;
    }

    /**
     * @param $recipient
     * @param null $expiredTime
     * @return array
     * @throws InvalidArgumentException
     */

    public function generateAndSave($recipient, $expiredTime = null)
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

    public function validateCodeId($codeId, $recipient)
    {
        return $this->setCodeId($codeId)->getRecipient() === $recipient;
    }

    /**
     * @param $codeId
     * @param $code
     * @return bool
     * @throws InvalidArgumentException
     */

    public function validateSaveCode($codeId, $code)
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

    public function validateAndDestroySaveCode($codeId, $code, $force = false)
    {
        $validateResult = $this->validateSaveCode($codeId, $code);
        if ($validateResult === true || $force === true) {
            $this->destroySaveCode($codeId);
        }
        return $validateResult;
    }
}
