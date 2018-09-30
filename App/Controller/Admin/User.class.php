<?php

namespace App\Controller\Admin;

/**
 * Description of User
 *
 * @author Administrator
 */
class User extends \App\Controller\Admin\Common
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{

	}

	public function logout()
	{
		$_SESSION['user'] = null;
		session_unset();
		session_destroy();
		setcookie(session_name(), '', time() - 1);
		$this->success('退出成功', '/index.html');
	}

}
