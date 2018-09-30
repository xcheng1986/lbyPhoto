<?php

namespace App\Controller\Admin;

/**
 * Description of Setting
 *
 * @author Administrator
 */
class Setting extends \App\Controller\Admin\Common
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$model = db();
		$setting = $model->select('select * from `setting` ');
		$R = [];
		if (!empty($setting))
			foreach ($setting as $v)
				$R[$v['name']] = $v;
		$this->assign('setting', $R);
		$this->display('Admin/Setting/index');
	}

	/**
	 *
	 */
	public function save()
	{
		$site_description = isset($_POST['site_description']) ? addslashes($_POST['site_description']) : '';
		$site_title = isset($_POST['site_title']) ? addslashes($_POST['site_title']) : '';
		$site_css = isset($_POST['site_css']) ? addslashes($_POST['site_css']) : '';
		if ($site_title == '')
			$this->error('站点名称为空');
		if ($site_description == '')
			$this->error('站点描述内容为空');

		$model = db();
		$model->update('update `setting` set `value`="' . $site_description . '" where name="site_description" limit 1');
		$model->update('update `setting` set `value`="' . $site_title . '" where name="site_title" limit 1');
		$model->update('update `setting` set `value`="' . $site_css . '" where name="site_css" limit 1');

		//保存图片
		if (isset($_FILES['background_img']) && $_FILES['background_img']['error'] === 0 && $_FILES['background_img']['size'] > 10240) {
			$this->saveImageFile($_FILES['background_img'], './img/background_img.jpg');
		}
		if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0 && $_FILES['site_logo']['size'] > 10240) {
			$this->saveImageFile($_FILES['site_logo'], './img/site_logo.jpg');
		}

		//保存配置到用户配置文件
		$this->saveUserConfigFile([
			'SET_VERSION' => date('YmdHis'),
			'SITE_TITLE' => $site_title,
			'SITE_DESCRIPTION' => $site_description,
			'SITE_CSS' => $site_css,
			'BACKGROUND_IMG' => '/img/background_img.jpg',
			'SITE_LOGO' => '/img/site_logo.jpg',
		]);

		$this->success('保存成功', '/Admin');
	}

	/**
	 * 保存配置到用户配置文件
	 * @param type $config
	 */
	private function saveUserConfigFile($config)
	{
		if (empty($config))
			return false;
		$cons = <<<EOF
<?php

return [

EOF;
		foreach ($config as $k => $v)
			$cons .= "\t'$k' => '$v',\r\n";

		$cons .= "];";
		file_put_contents(ROOT_PATH . '/App/Conf/user_setting.conf.php', $cons);
	}

	/**
	 * 保存图片文件
	 * @param type $file
	 * @param type $save_path
	 */
	private function saveImageFile($file, $save_path)
	{
		list($file_type, $file_extension_name) = explode('/', $file['type']);
		if ($file_type != 'image')
			$this->error('非图片文件');
		if ($file['size'] > 1024 * 1024 * 1)
			$this->error('图片文件限制在1M内');
		if (move_uploaded_file($file['tmp_name'], $save_path) == false)
			$this->error('图片上传出错');
		return true;
	}

}
