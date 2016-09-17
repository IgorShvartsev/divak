<?php


class IndexController extends \Controller
{
    /**
    * @var \UserModel 
    */
    protected $_userModel;
   
    /**
    *  Example of DI
    */
    public function __construct(\UserModel $userModel)
    {
        $this->_userModel = $userModel;
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
    *  GET /json-test
    */
    public function jsonTest()
    {	 
   	    return \Response::json(['test'=> 'Welcome']);
    }

    /**
    *  Example of using model
    *  
    *  GET /user/xxxx  where  xxxx  any user ID 
    */
    public function user()
    {
        $id = \Request::getParam('param1', 0);
        $user = $this->_userModel->find($id);
        \Debug::trace($user);

        // or using \Db
        $user = \Db::connection()
                    ->query("SELECT * FROM `user` WHERE `id` = ?")
                    ->fetch([$id]); 
        \Debug::trace($user);
    }
}
