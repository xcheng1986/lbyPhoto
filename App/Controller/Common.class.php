<?php

namespace App\Controller;

/**
 * 项目控制器总继承
 *
 * @author Administrator
 */
class Common extends \Core\Lib\Controller
{

	protected $user = [];

	public function __contruct()
	{
		parent::__contruct();
		$this->user = isset($_SESSION['user']) ? $_SESSION['user'] : ['status'=>'guest'];
	}

}
