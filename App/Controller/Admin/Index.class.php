<?php

namespace App\Controller\Admin;

/**
 * Description of Index
 *
 * @author Administrator
 */
class Index extends Common
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
		if (empty($user))
			$this->display('Admin/Login/login');
		else
			$this->display('Admin/Index/index');
	}

}
