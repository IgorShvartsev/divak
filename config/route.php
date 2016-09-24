<?php

return [
	'root' => ['method' => 'get', 'action' => 'admin/index/index', 'middleware' => ['before'=>'auth']],
];
