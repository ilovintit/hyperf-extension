<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Utils\Context;
use Iit\HyLib\Service\WeChat as WeChatService;
use EasyWeChat\MiniProgram\Application as MiniProgramApplication;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;
use EasyWeChat\Payment\Application as PaymentApplication;

class WeChat
{

    /**
     * @param string $name
     * @return OfficialAccountApplication
     */
    public static function officialAccount($name = 'default'): OfficialAccountApplication
    {
        return Context::getOrSet('weChatOfficialAccount' . ucfirst($name), function () use ($name) {
            return make(WeChatService::class, [
                'type' => WeChatService::TYPE_OFFICIAL_ACCOUNT,
                'name' => $name
            ])->app;
        });
    }

    /**
     * @param string $name
     * @return MiniProgramApplication
     */
    public static function miniProgram($name = 'default'): MiniProgramApplication
    {
        return Context::getOrSet('weChatMiniProgram' . ucfirst($name), function () use ($name) {
            return make(WeChatService::class, [
                'type' => WeChatService::TYPE_MINI_PROGRAM,
                'name' => $name
            ])->app;
        });
    }

    /**
     * @param string $name
     * @return PaymentApplication
     */
    public static function payment($name = 'default'): PaymentApplication
    {
        return Context::getOrSet('weChatPayment' . ucfirst($name), function () use ($name) {
            return make(WeChatService::class, [
                'type' => WeChatService::TYPE_PAYMENT,
                'name' => $name
            ])->app;
        });
    }
}
