<?php
/**
 * Date: 2020/8/12
 * @author Alfa
 */

namespace Alfa\Wechat\src;


use Illuminate\Support\ServiceProvider;

class WechatServiceProvider extends ServiceProvider
{

    /**
     * @retrun void
     */
    public function boot() :void{
        $this->publishes([
            __DIR__ . '/../config/wechat.php' => config_path("wechat.php")
        ], 'alfa-wechat-config');
    }

    /**
     * @retrun void
     */
    public function register() :void
    {
        $this->app->singleton(WechatRequest::class, function (){
            return new WechatRequest();
        });
    }
}