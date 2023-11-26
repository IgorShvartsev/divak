<?php
namespace Modules\Chat;

use \Db\PdoModel;

class ChatModel extends PdoModel
{
    public function getMessage()
    {
        return 'Hello World!';
    }
}
