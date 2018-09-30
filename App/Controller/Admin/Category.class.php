<?php

namespace App\Controller\Admin;

/**
 * Description of Category
 *
 * @author Administrator
 */
class Category extends Common
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
        $model = db();
        $categories = $model->select('select * from `categories` order by id desc');
        $IMAGE = new \App\Service\Image();
        //获取每个相册的最后上传的照片
        foreach ($categories ?: [] as &$c) {
            $cover_image_id = 0;
            if (isset($c['cover_image_id']) && $c['cover_image_id'] > 0)
                $cover_image_id = (int) $c['cover_image_id'];
            else
                $cover_image_id = (int) ($IMAGE->get_category_last_img_id($c['id']));

            //封面图片ID(方便后期扩展)
            $c['cover_image_id'] = $cover_image_id ?: 0;
            //封面图片url
            $c['cover_image_url'] = $IMAGE->get_category_cover_img_url($cover_image_id);
        }
        $this->assign('categories', $categories);
        $this->display('Admin/Category/list');
    }

	public function add()
	{
		$this->display('Admin/Category/add');
	}

	public function toAdd()
	{
		$name = isset($_POST['name']) ? strtolower(trim($_POST['name'])) : '';
		$comment = isset($_POST['comment']) ? $_POST['comment'] : '';
		$dir = isset($_POST['dir']) ? strtolower(trim($_POST['dir'])) : '';
		$is_public = isset($_POST['is_public']) ? ($_POST['is_public'] == 'public' ? 'public' : 'private') : 'public';
		$commentable = isset($_POST['commentable']) ? ($_POST['commentable'] == 'true' ? 'true' : 'false') : 'true';
		$image_order = isset($_POST['image_order']) ? intval($_POST['image_order']) : 1;
		$rank = isset($_POST['rank']) ? intval($_POST['rank']) : 100;
		$permalink = isset($_POST['permalink']) ? strtolower(trim($_POST['permalink'])) : '';

		if ($name == '' || iconv_strlen($name, 'UTF-8') > 30)
			$this->error('相册名称为空或者错误');
		if ($dir != '' && !preg_match('/^[a-z0-9_]{1,64}$/', $dir))
			$this->error('相册英文名称错误');
		if ($dir == '')
			$dir = substr(strtolower(md5(uniqid())), 8, 8);
		if ($permalink != '' && !preg_match('/^[a-z0-9_]{1,24}$/', $permalink))
			$this->error('短链接名称错误');

		$CATEGORY = new \App\Service\Category();
		//检查相册名称是否重复
		if ($CATEGORY->is_category_name_used($name))
			$this->error('相册名称重复');
		//检查英文名称是否重复
		if ($CATEGORY->is_dir_used($dir))
			$this->error('相册英文名称重复');
		//检查短链接名称是否重复
		if ($permalink != '' && $CATEGORY->is_permalink_used($permalink))
			$this->error('短链接名称重复');

		$res = $CATEGORY->add($name, $comment, '', $dir, $rank, $is_public, $commentable, $image_order, $permalink);
		if ($res)
			$this->success('添加成功', '/Admin/category');
		else
			$this->error('添加失败');
	}

	public function toEdit()
	{
		$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
		if ($category_id < 1)
			$this->error('请选择相册');

		$model = db();

		//获取相册信息
		$category_info = $model->find('select * from `categories` where id=' . $category_id . ' limit 1');
		if (empty($category_info))
			$this->error('相册未找到');

		$name = isset($_POST['name']) ? strtolower(trim($_POST['name'])) : '';
		$comment = isset($_POST['comment']) ? $_POST['comment'] : '';
		$rank = isset($_POST['rank']) ? intval($_POST['rank']) : 0;
		$is_public = isset($_POST['is_public']) ? ($_POST['is_public'] == 'public' ? 'public' : 'private') : 'public';
		$commentable = isset($_POST['commentable']) ? ($_POST['commentable'] == 'true' ? 'true' : 'false') : 'true';
		$image_order = isset($_POST['image_order']) ? intval($_POST['image_order']) : 1;
		$permalink = isset($_POST['permalink']) ? strtolower(trim($_POST['permalink'])) : '';

		if ($name == '' || iconv_strlen($name, 'UTF-8') > 30)
			$this->error('相册名称为空或者错误');
		if ($permalink != '' && !preg_match('/^[a-z0-9_]{1,24}$/', $permalink))
			$this->error('短链接名称错误');

		$CATEGORY = new \App\Service\Category();
		//检查相册名称是否重复
		if ($CATEGORY->is_category_name_used($name, $category_id))
			$this->error('相册名称重复');
		//检查短链接名称是否重复
		if ($permalink != '' && $CATEGORY->is_permalink_used($permalink, $category_id))
			$this->error('短链接名称重复');

		$res = $CATEGORY->update($category_id, $name, $comment, $rank, $is_public, $commentable, $image_order, $permalink);
		if ($res)
			$this->success('修改成功');
		else
			$this->error('修改失败');
	}

	/**
	 * 设置相册封面
	 */
	public function setCover()
	{
		$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
		if ($category_id < 1)
			$this->error('请选择相册');

		$img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;
		if ($img_id < 3)
			$this->error('请选择图片');

		$model = db();
		$info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
		if (!$info)
			$this->setResult(4, '图片未找到');

		$category_info = $model->find('select * from `categories` where id=' . $category_id . ' limit 1');
		if (empty($category_info))
			$this->error('相册未找到');


		$res = $model->update('update categories set cover_image_id=' . $img_id . ' where id=' . $category_id . ' limit 1');
		if ($res)
			$this->setResult(0, '设置相册封面成功');
		else
			$this->setResult(4, '设置相册封面失败');
	}

	public function images()
	{

		$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
		if ($category_id < 1)
			$this->error('请选择相册');

		$model = db();
		//获取相册信息
		$category_info = $model->find('select * from `categories` where id=' . $category_id . ' limit 1');
		if (empty($category_info))
			$this->error('相册未找到');
		$this->assign('category_id', $category_id);

		if ($category_info['is_public'] != 'public' && empty($_SESSION['user']))
			$this->error('未授权的访问');

		$this->assign('category_info', $category_info);

		//获取相册的图片列表
		$sql = 'select * from `images` where category_id=' . $category_id;
		if ($category_info['image_order'] != '')
			$sql .= ' order by ' . $this->getImgOrderByIndex($category_info['image_order']);

		$image_lists = $model->select($sql);
		$this->assign('image_lists', $image_lists);

		$this->display('Admin/Category/images');
	}

	public function delete()
	{

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
