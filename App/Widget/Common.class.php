<?php

namespace App\Widget;

/**
 * Description of Common
 *
 * @author lxc
 */
class Common extends \Core\Lib\Controller
{

	public function common_header()
	{
		$this->display('Pub/common_header');
	}

	public function common_logo()
	{
		$model = db();
		$setting_info = $model->select('select * from setting');
		$kyes = [];
		foreach ($setting_info ?: [] as $v)
			$kyes[$v['name']] = $v;

		$this->assign('keys', $kyes);
		$this->display('Pub/common_logo');
	}

	public function common_footer()
	{
		$this->display('Pub/common_footer');
	}

	public function admin_header()
	{
		$user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
		$this->assign('user', $user);

		$model = db();
		$categories = $model->select('select * from `categories` where is_public="public" order by rank desc');
		$this->assign('categories', $categories);

		$this->display('Pub/common_header');
	}

}
