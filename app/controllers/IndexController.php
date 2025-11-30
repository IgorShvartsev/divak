<?php

class IndexController extends \Controller
{
    /**
    * @var \UserModel 
    */
    protected $userModel;
   
    /**
    *  Example of DI
    */
    public function __construct(\UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
    *  Render default page 
    *  On default render method uses view template /views/[controller]/[method].phtml
    *
    *  GET /
    */
    public function index()
    {   
        $data = [];
        $this->render($data);
    }
   
    /**
    *  Example of JSON response
    *
    *  GET /json
    */
    public function jsonTest()
    {    
        return \Response::json(['test'=> 'Welcome']);
    }

    /**
     * Example of API request
     * 
     *  GET /api/xxxx  where xxxx any integer 
     */
    public function apiTest()
    {
        $id = \Request::getParam('param1', 1);

        $client = new \Library\Api\Rest\Client\RestClient(
            'Example',
            [
                'base_uri' => 'https://jsonplaceholder.typicode.com/'
            ]
        );
        
        $response = $client->query('posts', 'get', ['id' => $id]);

        \Debug::trace($response);
    }

    /**
    *  Example of using model
    *  
    *  GET /user/xxxx  where  xxxx  any user ID 
    */
    public function user()
    {
        $id = \Request::getParam('param1', 0);
        $user = $this->userModel->find($id);
        \Debug::trace($user);

        // or using \Db
        $user = \Db::connection()
                    ->query("SELECT * FROM `user` WHERE `id` = ?")
                    ->fetch([$id]); 
                    
        \Debug::trace($user);
    }
}
