<?php

namespace App\Controller;

/**
 * Description of category
 *
 * @author Administrator
 */
class Category extends Common
{

	public function __construct()
	{
		parent::__contruct();
		$user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
		$this->assign('user', $user);
	}

	public function index()
	{
		$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
		if ($category_id < 0)
			$this->error('参数错误');

		$model = db();

		//获取相册信息
		$category_info = $model->find('select * from `categories` where id=' . $category_id . ' limit 1');
		if (empty($category_info))
			$this->error('相册未找到');

		//非公开并且未登录的不能查看
		$user = $this->user;
		if ($category_info['is_public'] == 'private' && ($user['status']!='webmaster' && $user['status']!='admin')){
			$this->error('未授权的访问');
		}
		
		$this->assign('category_info', $category_info);

		//获取相册的图片列表
		$sql = 'select * from `images` where category_id=' . $category_id;
		if ($category_info['image_order'] != '')
			$sql .= ' order by ' . $this->getImgOrderByIndex($category_info['image_order']);

		$image_lists = $model->select($sql);
		$this->assign('image_lists', $image_lists);
		$this->display('Category/index');
	}

	/**
	 *
	 * @param type $index
	 * @return type
	 */
	private function getImgOrderByIndex($index)
	{
		$arr = config('IMAGE_ORDER_KEYS');
		return isset($arr[$index]) ? $arr[$index]['key'] : $arr[1]['key'];
	}

}
