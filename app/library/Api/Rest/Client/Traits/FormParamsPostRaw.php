<?php
namespace Library\Api\Rest\Client\Traits;

use GuzzleHttp\RequestOptions;
use Library\Api\Rest\Client\Enum\RequestMethodEnum;
use Library\Api\Rest\Client\Enum\ResponseTypeEnum;

/**
 * FormParamsPostRaw trait
 * 
 */
trait FormParamsPostRaw
{
    /**
     * Init properties
     */
    protected function initProperties()
    {
        $this->requestType = RequestOptions::FORM_PARAMS;
        $this->requestMethod = RequestMethodEnum::POST;
        $this->responseType = ResponseTypeEnum::RAW;
    }
}