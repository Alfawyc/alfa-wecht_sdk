<?php
/**
 * Date: 2020/8/12
 * @author Alfa
 */

namespace Alfa\Wechat\src;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

trait RequestTrait
{

    /**
     * @var Client\
     */
    protected $http;

    /**
     * @var
     */
    protected $handlerStack;

    protected $grantKey = 'access_token';

    /**
     * 重试次数
     *
     * @var int
     */
    protected $retryTimes = 3;

    /**
     * client 超时时间
     *
     * @var int
     */
    protected $timeout = 10;

    protected $responseJson = true;

    /**
     * 获取client
     *
     * @return Client
     */
    public function getHttpClient(){
        if(!is_null($this->http)){
            return $this->http;
        }
        $stack = $this->getHttpHandlerStack();
        $client = new Client(array('timeout' => 10 , 'handler' => $stack));
        $this->http = $client;

        return $client;
    }

    /**
     * 中间件
     *
     * @return HandlerStack
     */
    public function getHttpHandlerStack(){
        $stack = HandlerStack::create(new CurlHandler());
        $stack->push(Middleware::retry($this->retryHandler()));
        $stack->push($this->accessTokenMiddleware());

        return $stack;
    }

    /**
     * 重试
     *
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
     * @param $method
     * @param $url
     * @param $args
     * @return mixed|string
     * @throws \Exception
     */
    public function callApi($method , $url , $args){
        $client =$this->getHttpClient();

        $requestParams = ['method' => $method , 'url' => $url , 'param' => $args];
        Log::debug("wechat-sdk request params :". var_export($requestParams , true));
        try{
            $response = call_user_func_array(array($client , $method) , array($url , $args));
        }catch (\Exception $exception){
            throw new \Exception("Wechat SDK Request Error");
        }

        return $this->parseResult($response);
    }

    /** format response
     * @param ResponseInterface $response
     * @return mixed|string
     */
    public function parseResult(ResponseInterface $response){
        $headers = $response->getHeaders();
        $result = $response->getBody()->getContents();
        if($this->responseJson && strpos($headers['Content-Type'][0] , 'application/json') !== false){
            return json_decode($result , true);
        }

        return $result;
    }

    /**
     * @param $url
     * @param $params
     * @return mixed|string
     * @throws \Exception
     */
    public function get($url , $params){
        $args = [RequestOptions::QUERY => $params];

        return $this->callApi('get' , $url , $params);
    }

    /**
     * @param $url
     * @param $params
     * @return mixed|string
     * @throws \Exception
     */
    public function post($url , $params){
        $args = [RequestOptions::FORM_PARAMS => $params];

        return $this->callApi('post' , $url , $params);
    }

    /**
     * @param $url
     * @param $params
     * @return mixed|string
     * @throws \Exception
     */
    public function postJson($url , $params){
        $args = [RequestOptions::BODY => json_encode($params , JSON_UNESCAPED_UNICODE)];

        return $this->callApi('post' , $url , $params);
    }

    /**
     * 上传文件
     *
     * @param $url
     * @param $params
     * @return mixed|string
     * @throws \Exception
     */
    public function upload($url , $params){
        $argc = [RequestOptions::MULTIPART => $params];

        return $this->callApi('post' , $url , $params);
    }
}