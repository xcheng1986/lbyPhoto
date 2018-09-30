<?php

namespace App\Controller;

/**
 * Description of Index
 *
 * @author lxc
 */
class Index extends Common
{

    public function __construct()
    {
        parent::__contruct();
    }

    public function index()
    {
        $order_arr = [
            'rank' => '按照系统定义',
            'last_upload_time' => '按照最后发表',
            'create_time' => '按照创建时间',
        ];
        $order = $order = isset($_GET['order']) ? $_GET['order'] : 'rank';
        if (!in_array($order, array_keys($order_arr))) {
            $order = 'rank';
        }
        $this->assign('order', $order)->assign('order_arr', $order_arr);

        $is_public = 'public';
        $user = $this->user;
        if (!empty($user) && isset($user['status']) && ($user['status'] == 'webmaster' || $user['status'] == 'admin')) {
            $is_public = null;
        }

        //获取相册列表
        $CATEGORY = new \App\Service\Category();
        $categories = $CATEGORY->lists($order, $is_public);

        $IMAGE = new \App\Service\Image();
        //获取每个相册的封面照片
        foreach ($categories ?: [] as &$c) {
            $cover_image_id = 0;
            if (isset($c['cover_image_id']) && $c['cover_image_id'] > 0) {
                $cover_image_id = (int) $c['cover_image_id'];
            } else {
                $cover_image_id = (int) ($IMAGE->get_category_last_img_id($c['id']));
            }

            //封面图片ID(方便后期扩展)
            $c['cover_image_id'] = $cover_image_id ?: 0;
            //封面图片url
            $c['cover_image_url'] = $IMAGE->get_category_cover_img_url($cover_image_id, 90) ?: getImgUrl(0, 90);
        }

        $this->assign('categories', $categories);
        $this->display('Index/index');
    }

}
