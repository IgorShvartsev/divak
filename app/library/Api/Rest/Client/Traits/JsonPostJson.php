<?php
namespace Library\Api\Rest\Client\Traits;

use GuzzleHttp\RequestOptions;
use Library\Api\Rest\Client\Enum\RequestMethodEnum;
use Library\Api\Rest\Client\Enum\ResponseTypeEnum;

/**
 * JsonPostJson trait
 * 
 */
trait JsonPostJson
{
    /**
     * Init properties
     */
    protected function initProperties()
    {
        $this->requestType = RequestOptions::JSON;
        $this->requestMethod = RequestMethodEnum::POST;
        $this->responseType = ResponseTypeEnum::JSON;
    }
}
