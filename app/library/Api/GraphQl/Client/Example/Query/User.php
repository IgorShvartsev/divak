<?php
namespace Library\Api\GraphQl\Client\Dms\Query;

use Library\Api\GraphQl\Client\Contract\BuildInterface;
use GraphQL\Query;

/**
 * User query builder
 * 
 * @see https://github.com/mghoneimy/php-graphql-client
 */
class CurrentUser implements BuildInterface
{   
    /**
     * @var array
     */
    protected $variables = [];

    /**
     * Build 
     * @param array $params
     * @return mixed
     */
    public function build(array $params = [])
    {
        return (new Query('me'))
            ->setSelectionSet([
                'id',
                'name',
                'email',
                'roleId',
                (new Query('role'))
                    ->setSelectionSet([
                        'id',
                        'name'
                    ]),
            ]);
    }

    /**
     * Get variables
     * 
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}