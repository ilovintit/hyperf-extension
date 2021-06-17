<?php
declare(strict_types=1);

namespace Iit\HyLib\Utils;

use Hyperf\Utils\Context;
use Iit\HyLib\Service\WeChat as WeChatService;
use EasyWeChat\MiniProgram\Application as MiniProgramApplication;
use EasyWeChat\OfficialAccount\Application as OfficialAccountApplication;
use EasyWeChat\Payment\Application as PaymentApplication;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

class WeChat
{

    /**
     * @param string $name
     * @return \EasyWeChat\OfficialAccount\Application
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
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public static function officialAccountNotify($name = 'default'): OfficialAccountApplication
    {
        $app = self::officialAccount($name);
        $app->rebind('request', self::request());
        return $app;
    }

    /**
     * @param string $name
     * @return \EasyWeChat\MiniProgram\Application
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
     * @return \EasyWeChat\MiniProgram\Application
     */
    public static function miniProgramNotify($name = 'default'): MiniProgramApplication
    {
        $app = self::miniProgram($name);
        $app->rebind('request', self::request());
        return $app;
    }

    /**
     * @param string $name
     * @return \EasyWeChat\Payment\Application
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

    /**
     * @param string $name
     * @return \EasyWeChat\Payment\Application
     */
    public static function paymentNotify($name = 'default'): PaymentApplication
    {
        $app = self::payment($name);
        $app->rebind('request', self::request());
        return $app;
    }

    /**
     * 转换框架请求为类库请求
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function request(): Request
    {
        $get = H::request()->getQueryParams();
        $post = H::request()->getParsedBody();
        $cookie = H::request()->getCookieParams();
        $uploadFiles = H::request()->getUploadedFiles() ?? [];
        $server = H::request()->getServerParams();
        $xml = H::request()->getBody()->getContents();
        $files = [];
        /** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
        foreach ($uploadFiles as $k => $v) {
            $files[$k] = $v->toArray();
        }
        $request = new Request($get, $post, [], $cookie, $files, $server, $xml);
        $request->headers = new HeaderBag(H::request()->getHeaders());
        return $request;
    }


}
