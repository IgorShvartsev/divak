<?php
namespace Admin;

class IndexController extends \Controller
{
    public function index()
    {
        $data = ['title' => 'Here is Admin page'];
        $this->render($data);
    }
}
