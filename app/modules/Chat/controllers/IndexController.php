<?php
namespace Modules\Chat;

class IndexController extends \Controller
{   
    protected $chatModel;

    public function __construct(\Modules\Chat\ChatModel $chatModel)
    {
        $this->chatModel = $chatModel;
    }
    public function index()
    {
        $data = [
            'message' => $this->chatModel->getMessage()
        ];
        $this->render($data);
    }
}
