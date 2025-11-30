<?php
namespace Library\Api\Rest\Client\Traits;

use GuzzleHttp\RequestOptions;
use Library\Api\Rest\Client\Enum\RequestMethodEnum;
use Library\Api\Rest\Client\Enum\ResponseTypeEnum;

/**
 * FormParamsPostJson trait
 * 
 */
trait FormParamsPostJson
{
    /**
     * Init properties
     */
    protected function initProperties()
    {
        $this->requestType = RequestOptions::FORM_PARAMS;
        $this->requestMethod = RequestMethodEnum::POST;
        $this->responseType = ResponseTypeEnum::JSON;
    }
}
