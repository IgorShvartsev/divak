<?php
namespace Library\Api\GraphQl\Client\Contract;

/**
 * Build Interface
 */
interface BuildInterface
{
    /**
     * Build 
     * @param array $params
     * @return mixed
     */
    public function build(array $params = []);

    /**
     * Get variables
     * 
     * @return array
     */
    public function getVariables();
}