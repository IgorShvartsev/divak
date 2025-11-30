<?php
namespace Library\Api\Rest\Client\Traits;

use GuzzleHttp\RequestOptions;
use Library\Api\Rest\Client\Enum\RequestMethodEnum;
use Library\Api\Rest\Client\Enum\ResponseTypeEnum;

/**
 * QueryGetJson trait
 * 
 */
trait QueryGetJson
{
    /**
     * Init properties
     */
    protected function initProperties()
    {
        $this->requestType = RequestOptions::QUERY;
        $this->requestMethod = RequestMethodEnum::GET;
        $this->responseType = ResponseTypeEnum::JSON;
    }
}
