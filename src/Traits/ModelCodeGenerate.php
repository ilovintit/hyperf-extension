<?php
declare(strict_types=1);

namespace App\Extension\Traits;

use App\Extension\Exceptions\CustomException;
use App\Utils\CodeGenerator;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Database\Model\SoftDeletingScope;

/**
 * Trait ModelCodeGenerate
 * @package App\Extension\Traits
 */
trait ModelCodeGenerate
{
    /**
     * @return string
     */

    protected function generateCodeKey()
    {
        return 'code';
    }

    /**
     * @return string
     */

    protected function generateCodeUniqueKey()
    {
        return self::class . ':' . $this->generateCodePrefix();
    }

    /**
     * @return int
     */

    protected function generateCodeType()
    {
        return CodeGenerator::TYPE_NUMBER_AND_LETTER;
    }

    /**
     * @return int|null
     */

    protected function generateCodeMaxLength()
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
     * @return string|null
     */

    protected function generateCode()
    {
        return CodeGenerator::getUniqueCode($this->generateCodeUniqueKey(), function () {
            return $this->maxCode();
        }, $this->generateCodeLength(), $this->generateCodeType(), $this->generateCodePrefix(),
            $this->generateCodeFirstMin(), $this->generateCodeFirstMax());
    }

    /**
     * @return false|int|string
     */

    protected function maxCode()
    {
        if ($maxModel = self::query()->withoutGlobalScope(SoftDeletingScope::class)
            ->orderByDesc($this->generateCodeKey())->first()
        ) {
            return substr($maxModel[$this->generateCodeKey()], strlen($this->generateCodePrefix()));
        }
        return 0;
    }

    /**
     * @return string
     */

    protected function generateCodePrefix()
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

    protected function generateCodeLength()
    {
        $maxLength = $this->generateCodeMaxLength() === null ? config('tools.model_code_length') : $this->generateCodeMaxLength();
        $length = $maxLength - strlen($this->generateCodePrefix());
        if ($length <= 0) {
            throw new CustomException('Model Length Is Too Short.');
        }
        return $length;
    }
}
