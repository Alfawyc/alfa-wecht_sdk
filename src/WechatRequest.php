<?php
/**
 * Date: 2020/8/12
 * @author Alfa
 */

namespace Alfa\Wechat\src;


use Alfa\Wechat\src\wechat\BaseClient;

class WechatRequest extends BaseClient
{
    private $apiQueryUrl = 'https://api.weixin.qq.com/';

    /**
     * @throws \Exception
     */
    public function getMenu(){
        $uri = $this->apiQueryUrl . 'cgi-bin/get_current_selfmenu_info';
        $data = $this->get($uri , []);
        return $data;
    }
}