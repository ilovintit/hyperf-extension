<?php

declare(strict_types=1);

namespace App\Extension\Listener;

use App\Extension\Exceptions\CustomException;
use App\Utils\VerificationCodeManager;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event)
    {
        /**  @var ValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $event->validatorFactory;
        //注册手机号码验证器
        $validatorFactory->extend('zh_mobile', function ($attribute, $value, $parameters, $validator) {
            return is_numeric($value) && strlen($value) === 11;
        });
        //注册验证码校验验证器
        $validatorFactory->extend('verification_code', function ($attribute, $value, $parameters, $validator) {
            if (!isset($parameters[0])) {
                throw new CustomException('短信验证码验证器第一个参数必须传入');
            }
            $paramValue = isset($validator->getData()[$parameters[0]]) ? $validator->getData()[$parameters[0]] : null;
            if (!empty($paramValue)) {
                return make(VerificationCodeManager::class)->validateAndDestroySaveCode($paramValue, $value);
            }
            return false;
        });
        //注册验证码ID验证器
        $validatorFactory->extend('verification_code_id', function ($attribute, $value, $parameters, $validator) {
            if (!isset($parameters[0])) {
                throw new CustomException('短信验证码ID验证器第一个参数必须传入');
            }
            $paramValue = isset($validator->getData()[$parameters[0]]) ? $validator->getData()[$parameters[0]] : null;
            if (!empty($paramValue)) {
                return make(VerificationCodeManager::class)->validateCodeId($value, $paramValue);
            }
            return false;
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :foo 占位符
//        $validatorFactory->replacer('foo', function ($message, $attribute, $rule, $parameters) {
//            return str_replace(':foo', $attribute, $message);
//        });
    }
}
