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

    /**
     * 带参数临时二维码
     *
     * @param string $key 关键词
     * @param int $second 过期时间
     * @return array
     * @throws \Exception
     */
    public function getQrCode($key , $second = 1800){
        $params = [
            'expire_seconds' => $second,
            'action_name' => 'QR_STR_SCENE',
            'action_info' => ['scene' => ['scene_str' => $key]]
        ];
        $result = $this->post($this->apiQueryUrl . 'cgi-bin/qrcode/create' , $params);

        return ['url' => $result['url'] , 'key' => $key];
    }

    /**
     * 获取用户信息
     *
     * @param string $openId
     * @param string $lang
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserInfo($openId , $lang = 'zh_CN'){
        $params = [
            'openid' => $openId,
            'lang' => $lang
        ];
        $result = $this->get($this->apiQueryUrl . 'cgi-bin/user/info' , $params);

        return $result;
    }

    /**
     * 获取用户列表
     *
     * @param string $nextOpenid
     * @return mixed|string
     * @throws \Exception
     */
    public function getUserList($nextOpenid = ''){
        $params = [
            'next_openid' => $nextOpenid
        ];
        $result = $this->get($this->apiQueryUrl . 'cgi-bin/user/get' , $params);

        return $result;
    }

    /**
     * 获取黑名单列表
     *
     * @param string $beginOpenid
     * @return mixed|string
     * @throws \Exception
     */
    public function getBlackList($beginOpenid = ''){
        $params = [
            'begin_openid' => $beginOpenid
        ];
        $result = $this->post($this->apiQueryUrl . '/cgi-bin/tags/members/getblacklist' , $params);

        return $result;
    }

    /** 拉黑用户
     * @param string|array $openidList
     * @return mixed|string
     * @throws \Exception
     */
    public function batchBlacklistUser($openidList){
        $openidList = is_array($openidList) ? $openidList : [$openidList];
        $params = [
            'openid_list' => $openidList
        ];
        $result = $this->post($this->apiQueryUrl . 'cgi-bin/tags/members/batchblacklist' , $params);

        return $result;
    }

    /**
     * 取消拉黑用户
     *
     * @param string|array $openidList
     * @return mixed|string
     * @throws \Exception
     */
    public function batchUnBlaclListUser($openidList){
        $openidList = is_array($openidList) ? $openidList : [$openidList];
        $params = [
            'openid_list' =>$openidList
        ];
        $result = $this->post($this->apiQueryUrl . 'cgi-bin/tags/members/batchunblacklist' , $params);

        return $result;
    }

    /**
     * 上传其他类型永久素材
     *
     * @param $path
     * @param string $type | image , voice , video, thumb
     * @return mixed|string
     * @throws \Exception
     */
    public function addMaterial($path , $type = 'image'){
        $params = [
            [
                'name' => 'media',
                'contents' => fopen($path , 'r')
            ],
            [
                'name' => 'type',
                'contents' => $type
            ]
        ];
        $result = $this->upload($this->apiQueryUrl . 'cgi-bin/material/add_material' , $params);

        return $result;
    }

    /**
     * 获取永久素材
     *
     * @param $mediaId
     * @return mixed|string
     * @throws \Exception
     */
    public function getMaterial($mediaId){
        $params = [
            'media_id' => $mediaId
        ];
        $result = $this->post($this->apiQueryUrl . 'cgi-bin/material/get_material'  , $params);

        return $result;
    }

    /**
     * 获取永久素材列表
     *
     * @param $type | image , video , voice , news
     *
     * @param int $offset
     * @param int $count
     * @return mixed|string
     * @throws \Exception
     */
    public function getMaterialList($type , $offset = 0 , $count = 20){
        $params = [
            'type' => $type,
            'offset' => $offset,
            'count' => $count
        ];
        $result = $this->postJson($this->apiQueryUrl . 'cgi-bin/material/batchget_material' , $params);

        return $result;
    }

    /**
     * 上传临时素材
     *
     * @param $path
     * @param string $type
     * @return mixed|string
     * @throws \Exception
     */
    public function uploadTemp($path , $type = 'image'){
        $params = [
            [
                'name' => 'media',
                'contents' => fopen($path , 'r')
            ],
            [
                'name' => 'type',
                'contents' => $type
            ]
        ];
        $result = $this->upload($this->apiQueryUrl . 'cgi-bin/media/upload' , $params);

        return $result;
    }

    /**
     * 获取临时素材
     *
     * @param $mediaid
     * @return mixed|string
     * @throws \Exception
     */
    public function getTempMedia($mediaid){
        $params = [
            'media_id' => $mediaid
        ];
        $result = $this->get($this->apiQueryUrl . 'cgi-bin/media/get' , $params);

        return $result;
    }

    /**
     * 获取网页授权access_token
     *
     * @param $code
     * @return mixed|string
     * @throws \Exception
     */
    public function getAuthToken($code){
        $params = [
            'appid' => config('wechat.app_id'),
            'secret' => config('wechat.app_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $result = $this->get($this->apiQueryUrl .'sns/oauth2/access_token' . $params);

        return $result;
    }
}