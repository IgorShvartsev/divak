<?php
namespace Library\Api\GraphQl\Client\Example\Mutation;

use Library\Api\GraphQl\Client\Contract\BuildInterface;
use GraphQL\Mutation;
use GraphQL\Variable;

/**
 * Login mutation builder
 * 
 * @see https://github.com/mghoneimy/php-graphql-client
 */
class Login implements BuildInterface
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
        $this->variables = [
            'input' => [
                'login' => $params['login'] ?? '',
                'password' => $params['password'] ?? '',
            ]
        ];

        return (new Mutation('login'))
            ->setVariables([new Variable('input', 'AuthInput', true)])
            ->setArguments(['authInput' => '$input'])
            ->setSelectionSet([
                'token',
                'userId',
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