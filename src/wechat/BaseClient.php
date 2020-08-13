<?php
/**
 * Date: 2020/8/12
 * @author Alfa
 */

namespace Alfa\Wechat\src\wechat;


use Alfa\Wechat\src\RequestTrait;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

abstract class BaseClient
{
    use RequestTrait;

    /**
     * @return \Closure
     */
    protected function accessTokenMiddleware(){
        return function (callable $handler){
            return function (RequestInterface $request , array $options) use($handler){
                $token = $this->getToken();
                $request = $request->withUri(Uri::withQueryValue($request->getUri() , 'access_token' , $token));
                return $handler($request , $options);
            };
        };
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(){
        $accessToken = new AccessToken();

        return $accessToken->getToken();
    }

}