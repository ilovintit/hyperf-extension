<?php
declare(strict_types=1);

namespace Iit\HyLib\Model;

use Iit\HyLib\Exceptions\CustomException;
use Hyperf\Database\Model\SoftDeletingScope;

/**
 * Trait ModelCodeGenerate
 * @package Iit\HyLib\Traits
 */
trait ModelCodeGenerate
{
    /**
     * @return string
     */
    protected function generateCodeKey(): string
    {
        return 'code';
    }

    /**
     * @return string
     */
    protected function generateCodeUniqueKey(): string
    {
        return self::class . ':' . $this->generateCodePrefix();
    }

    /**
     * @return int
     */
    protected function generateCodeType(): int
    {
        return CodeGenerator::TYPE_NUMBER_AND_LETTER;
    }

    /**
     * @return int|null
     */
    protected function generateCodeMaxLength(): ?int
    {
        return null;
    }

    /**
     * @return null
     */
    protected function generateCodeFirstMin()
    {
        return null;
    }

    /**
     * @return null
     */
    protected function generateCodeFirstMax()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function generateCode(): string
    {
        return CodeGenerator::getUniqueCode($this->generateCodeUniqueKey(), $this->maxCode(),
            $this->generateCodeLength(), $this->generateCodeType(), $this->generateCodePrefix(),
            $this->generateCodeFirstMin(), $this->generateCodeFirstMax());
    }

    /**
     * @return string
     */
    protected function maxCode(): string
    {
        if ($maxModel = self::query()->withoutGlobalScope(SoftDeletingScope::class)
            ->orderByDesc($this->generateCodeKey())->first()
        ) {
            return substr($maxModel[$this->generateCodeKey()], strlen($this->generateCodePrefix()));
        }
        return '0';
    }

    /**
     * @return string
     */
    protected function generateCodePrefix(): string
    {
        $names = explode('\\', self::class);
        if (preg_match_all('/[A-Z]/', end($names), $matchItems) === 0) {
            throw new CustomException('Could Not Get Model Name To Code Prefix.');
        }
        return implode('', $matchItems[0]);
    }

    /**
     * @return int
     */
    protected function generateCodeLength(): int
    {
        $maxLength = $this->generateCodeMaxLength() === null ? config('tools.model_code_length') : $this->generateCodeMaxLength();
        $length = $maxLength - strlen($this->generateCodePrefix());
        if ($length <= 0) {
            throw new CustomException('Model Length Is Too Short.');
        }
        return $length;
    }
}
