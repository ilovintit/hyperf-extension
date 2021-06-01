<?php
declare(strict_types=1);

namespace Iit\HyLib\Model;

use Closure;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Utils\ApplicationContext;
use Iit\HyLib\Exceptions\CustomException;
use Iit\HyLib\RedisLock\LockTimeoutException;
use Iit\HyLib\RedisLock\RedisLock;
use Redis;

/**
 * Class CodeGenerator
 * @package Iit\HyLib\Model
 */
class CodeGenerator
{
    const TYPE_ONLY_NUMBER = 1;

    const TYPE_ONLY_LETTER = 2;

    const TYPE_NUMBER_AND_LETTER = 3;

    /**
     * @var string[]
     */
    protected static array $numberMap = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9'
    ];

    /**
     * @var string[]
     */
    protected static array $letterMap = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z'
    ];

    /**
     * @param $key
     * @return string
     */
    protected static function cacheTags($key): string
    {
        return config('cache.default.prefix') . 'code-generator:' . $key;
    }

    /**
     * @param $type
     * @return array
     */
    protected static function getMapList($type): array
    {
        if ($type === self::TYPE_ONLY_NUMBER) {
            return self::$numberMap;
        } elseif ($type === self::TYPE_ONLY_LETTER) {
            return self::$letterMap;
        } elseif ($type === self::TYPE_NUMBER_AND_LETTER) {
            return array_merge(self::$numberMap, self::$letterMap);
        } else {
            return [];
        }
    }

    /**
     * @param $map
     * @param $max
     * @param $reciprocal
     * @return bool|string
     */
    protected static function getNextCharacter($map, $max, $reciprocal)
    {
        $character = substr($max, -$reciprocal, 1);
        $now = self::getPosition($character, $map);
        if ($now === false) {
            return false;
        }
        return $now === max(array_flip($map)) ? true : $map[$now + 1];
    }

    /**
     * @param $character
     * @param $map
     * @return false|int|string
     */
    protected static function getPosition($character, $map)
    {
        return array_search(strtoupper($character), $map, true);
    }

    /**
     * @param $max
     * @param $len
     * @param int $type
     * @param string $prefix
     * @param int $firstMin
     * @param string $firstMax
     * @return null|string
     */
    public static function getNext($max, $len, $type = self::TYPE_NUMBER_AND_LETTER, $prefix = '', $firstMin = null, $firstMax = null): ?string
    {
        if ($len < 1 || !is_integer($len)) {
            throw new GenerateCodeException('要求生成编码的长度必须是整数并且不能小于1');
        }
        if (strlen($max) > $len) {
            throw new GenerateCodeException('传入的当前编码字符长度超过要求生成的字符长度');
        }
        if (empty($map = self::getMapList($type))) {
            throw new GenerateCodeException('不支持的字符集类型');
        }
        $firstMin = $firstMin === null ? reset($map) : $firstMin;
        $firstMax = $firstMax === null ? end($map) : $firstMax;
        $max = str_pad($max, $len, '0', STR_PAD_LEFT);
        $first = substr($max, 0, 1);
        $firstPosition = self::getPosition($first, $map);
        if ($firstPosition === false) {
            throw new GenerateCodeException('当前编码首字符不存在于字符集内', [
                'first' => $first,
                'map' => $map,
                'firstPosition' => $firstPosition
            ]);
        }
        $firstMinPosition = self::getPosition($firstMin, $map);
        if ($firstMinPosition === false) {
            throw new GenerateCodeException('当前编码首字符最小字符不存在于字符集内', [
                'firstMin' => $firstMin,
                'map' => $map,
                'firstMinPosition' => $firstMinPosition
            ]);
        }
        $firstMaxPosition = self::getPosition($firstMax, $map);
        if ($firstMaxPosition === false) {
            throw new GenerateCodeException('当前编码首字符最大字符不存在于字符集内', [
                'firstMax' => $firstMax,
                'map' => $map,
                'firstMaxPosition' => $firstMaxPosition
            ]);
        }
        if ($firstPosition < $firstMinPosition) {
            $max = $firstMin . substr($max, 1, $len - 1);
        }
        if ($firstPosition > $firstMaxPosition) {
            throw new GenerateCodeException('需要生成的编码首字符超出定义字符集的最大字符,请更改字符集或增加编码长度', [
                'firstPosition' => $firstPosition,
                'firstMaxPosition' => $firstMaxPosition,
            ]);
        }
        $final = [];
        for ($i = 1; $i <= $len; $i++) {
            $result = self::getNextCharacter($map, $max, $i);
            if ($result === false) {
                throw new GenerateCodeException('第' . $i . '位字符获取下一个字符失败');
            }
            if ($result !== true) {
                $final[] = $result;
                break;
            }
            if ($result === true) {
                $final[] = $map[0];
            }
        }
        $diff = $len - count($final);
        $finalStr = substr($max, 0, $diff) . implode('', array_reverse($final));
        $firstPosition = self::getPosition(substr($finalStr, 0, 1), $map);
        if ($firstPosition > $firstMaxPosition) {
            throw new GenerateCodeException('生成的编码首字符超出定义字符集的最大字符,请更改字符集或增加编码长度');
        }
        return $prefix . $finalStr;
    }

    /**
     * @return RedisProxy|Redis|null
     */
    protected static function redis()
    {
        return ApplicationContext::getContainer()
            ->get(RedisFactory::class)
            ->get('default');
    }

    /**
     * @param $type
     * @return int|null
     */
    protected static function integerMap($type): ?int
    {
        $map = [
            self::TYPE_ONLY_NUMBER => 10,
            self::TYPE_ONLY_LETTER => 26,
            self::TYPE_NUMBER_AND_LETTER => 36,
        ];
        return $map[$type] ?? null;
    }

    /**
     * @param $code
     * @param $type
     * @return int
     */
    public static function convertCodeToInteger($code, $type): int
    {
        if (!$coefficient = self::integerMap($type)) {
            throw new GenerateCodeException('从字符翻译数字失败');
        }
        $map = self::getMapList($type);
        $length = strlen($code);
        $return = 0;
        for ($i = 1; $i <= $length; $i++) {
            $singleCode = substr($code, -$i, 1);
            $base = pow($coefficient, $i - 1);
            $position = self::getPosition($singleCode, $map);
            $return += $base * $position;
        }
        return $return;
    }

    /**
     * @param $integer
     * @param $type
     * @param $length
     * @return string
     */
    public static function convertIntegerToCode($integer, $type, $length): string
    {
        if (!$coefficient = self::integerMap($type)) {
            throw new GenerateCodeException('从数字翻译字符失败');
        }
        $map = self::getMapList($type);
        $code = [];
        $hasConvert = 0;
        $i = 1;
        do {
            $remainder = ($integer % pow($coefficient, $i)) - $hasConvert;
            $offset = $remainder / pow($coefficient, $i - 1);
            $code[] = $map[$offset];
            $hasConvert += $remainder;
            $i++;
        } while ($i <= $length);
        return implode("", array_reverse($code));
    }

    /**
     * @param $uniqueKey
     * @param Closure|string $maxCode
     * @param $len
     * @param int $type
     * @param string $prefix
     * @param int $firstMin
     * @param string $firstMax
     * @return string
     */
    public static function getUniqueCode($uniqueKey, $maxCode, $len, $type = self::TYPE_NUMBER_AND_LETTER, $prefix = '', $firstMin = null, $firstMax = null): ?string
    {
        $cacheKey = self::cacheTags($uniqueKey);
        $redisLock = RedisLock::create($uniqueKey, 3);
        $tips = 3;
        $nowMaxCode = null;
        do {
            if (self::redis()->exists($cacheKey)) {
                $nowMaxCode = intval(self::redis()->get($cacheKey));
                self::redis()->incr($cacheKey);
                break;
            } elseif ($redisLock->get()) {
                $nowMaxCode = self::convertCodeToInteger(value($maxCode), $type);
                self::redis()->set($cacheKey, strval($nowMaxCode));
                $redisLock->release();
                break;
            } else {
                $tips--;
                usleep(250 * 1000);
            }
        } while ($tips > 0);
        if ($nowMaxCode === null) {
            throw new CustomException('获取最大编码失败,请重试.');
        }
        return self::getNext(self::convertIntegerToCode($nowMaxCode, $type, $len), $len, $type, $prefix, $firstMin, $firstMax);
    }

}
