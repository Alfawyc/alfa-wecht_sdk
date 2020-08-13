<?php
/**
 * Date: 2020/8/12
 * @author Alfa
 */

namespace Alfa\Wechat\src\wechat;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;

class AccessToken
{
    protected $appId;

    protected $appSecret;

    protected $http;

    protected $handlerStack;

    protected $timeout = 10;

    private $apiQueryUrl = 'https://api.weixin.qq.com/';

    const WECHAT_SDK_ACCESS_TOKEN_CACHE = 'wechat_sdk_access_token_cache';

    public function __construct()
    {
        $this->appId = config("wechat.app_id");
        $this->appSecret = config('wechat.app_secret');
        dd($this->appId);
    }

    /**
     * @return Client
     */
    public function getHttpClient(){
        if(!is_null($this->http)){
            return $this->http;
        }
        $stack = $this->getHttpHandlerStack();
        $stack->push(Middleware::retry($this->retryHandler()));
        $client = new Client([
            'timeout' => $this->timeout,
            'handler' => $stack
        ]);
        $this->http = $client;

        return $client;
    }

    /**
     * @return HandlerStack
     */
    public function getHttpHandlerStack(){
        $stack = HandlerStack::create(new CurlHandler());
        $this->handlerStack = $stack;

        return $stack;
    }

    /**
     * @return \Closure
     */
    public function retryHandler(){
        return function ($retries , Request $request , Response $response = null , RequestException $exception = null){
            if($retries >= 3){
                return false;
            }
            if ($exception instanceof ConnectException){
                return true;
            }
            if($response->getStatusCode() >= 500){
                return true;
            }

            return true;
        };
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestToken(){
        $client = $this->getHttpClient();
        $url = $this->apiQueryUrl . 'cgi-bin/token';
        $params = [
            'grant_type' => 'client_credential',
            'appid' => $this->appId,
            'secret' => $this->appSecret
        ];
        try{
            $response = $client->request('get' , $url , ['query' => $params]);
        }catch (\Exception $exception){
            throw  new \Exception('FAIL GET ACCESS_TOEKN' , 101);
        }
        $response = json_decode($response->getBody()->getContents(), true);
        if (!isset($response['access_token'])) {
            throw new \Exception('Error getting token', 102);
        }
        Cache::put(self::WECHAT_SDK_ACCESS_TOKEN_CACHE , $response['access_token'] , now()->addMinute(($response['expires_in'] - 600) / 60 ));

        return $response['access_token'];
    }

    /**
     * @param bool $forceRefresh
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken($forceRefresh = false){
        if($forceRefresh == false && Cache::has(self::WECHAT_SDK_ACCESS_TOKEN_CACHE)){
            return Cache::get(self::WECHAT_SDK_ACCESS_TOKEN_CACHE);
        }
        $token = $this->requestToken();

        return $token;
    }
}