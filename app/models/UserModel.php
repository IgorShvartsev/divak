<?php

use \Db\PdoModel;

class UserModel extends PdoModel
{
	public function find($id)
	{
		$userEntry  = $this->query('SELECT * FROM `user` WHERE `id` = ?')
			 		       ->fetch([$id]);
		return $userEntry;	 		  
	}
}
