<?php

namespace App\Controller\Admin;

use App\Service\Oss;

/**
 * Description of Photo
 *
 * @author Administrator
 */
class Image extends \App\Controller\Admin\Common
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 修复图片
     */
    public function checkAll()
    {
        $model = db();
        //正向检查
        $list = $model->select('select * from `images` where 1 ');
        foreach ($list as $v) {
            if (!is_file($v['file_path'])) {
                $sql = 'delete from `image` where id=' . $v['id'];
                $model->update($sql);
                continue;
            }

            //删除缩略图
            $slt_list = glob($v['file_path'] . '_*');
            if (!empty($slt_list)) {
                foreach ($slt_list as $s) {
                    @unlink($s);
                }
            }

            list($width, $height, $type, $attr) = getimagesize($v['file_path']);
            $file_size = filesize($v['file_path']);
            $sql = 'update `images` set filesize=' . $file_size . ',width=' . $width . ',height=' . $height . ' where id=' . $v['id'] . ' limit 1';
        }

        //反向检查
        $categories_list = $model->select('select * from `categories` where 1 ');
        if (empty($categories_list)) {
            delete_dir(config('IMAGE_UPLOAD_DIR') . DIRECTORY_SEPARATOR);
            $this->success('修复完毕', '/Admin');
        }

        $dirs = [];
        foreach ($categories_list as $v) {
            $dirs[$v['dir']] = $v['dir'];
        }

        $dirs_exists = glob(config('IMAGE_UPLOAD_DIR') . DIRECTORY_SEPARATOR . '*');
        foreach ($dirs_exists as $f) {
            $dir = basename($f);
            if (!isset($dirs[$dir])) {
                delete_dir($f);
                continue;
            }
            $files = glob($f . DIRECTORY_SEPARATOR . '*');
            foreach ($files ?: [] as $rf) {
                if (!$model->find('select id from `images` where file_path="' . $rf . '" limit 1')) {
                    unlink($rf);
                }
            }
        }

        $this->success('修复图片完毕', '/Admin');
    }

    /**
     * 图片管理(导航及总的统计信息)
     */
    public function index()
    {
        $this->display('Admin/Image/index');
    }

    public function upload()
    {
        $model = db();
        $categories = $model->select('select * from `categories` order by id desc');
        $this->assign('categorys', $categories);
        $this->display('Admin/Image/upload');
    }

    /**
     * 上传
     */
    public function toUpload()
    {
        $category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
        if ($category_id < 1) {
            $this->setResult(1, '请选择相册');
        }

        $CATEGORY = new \App\Service\Category();
        $category_info = $CATEGORY->info($category_id);
        if (empty($category_info)) {
            $this->setResult(2, '相册未找到');
        }

        //调用上传
        $IMAGE = new \App\Service\Image();
        $R = $IMAGE->upload_img_to_local($_FILES['file'], $category_info['dir']);
        if (!$R) {
            $this->setResult(3, '上传失败');
        }
        if ($R['status'] != 0) {
            $this->setResult($R['status'], $R['info']);
        }

        //记录数据库
        $insert_id = $IMAGE->add($R['data']['file_path'], $R['data']['file_name'], $R['data']['datetime_original'], $_SESSION['user']['id'], $R['data']['filesize'], $R['data']['width'], $R['data']['height'], $category_id, $R['data']['exif_info'], $R['data']['md5_file']);
        if (!$insert_id) {
            $this->setResult(4, '上传失败[insert error]');
        }

        //上传到阿里云OSS
        (new Oss())->uploadFile($category_info['dir'], realpath($R['data']['file_path']), 'img');

        //修改相册最后更新时间
        $CATEGORY->update_category_last_upload_time($category_id);

        $this->setResult(0, '上传成功', ['url' => getImgUrl($insert_id, 90)]);
    }

    /**
     * 所有图片列表(分页)
     */
    public function lists()
    {
        $this->display('Admin/Image/lists');
    }

    /**
     * AJAX获取图片的信息
     */
    public function info()
    {
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;
        $model = db();
        $info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
        if (!$info) {
            $this->setResult(1, '图片未找到');
        }

        $this->setResult(0, 'OK', $info);
    }

    /**
     * 修改图片信息
     */
    public function edit()
    {
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;
        $model = db();
        $info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
        if (!$info) {
            $this->setResult(1, '图片未找到');
        }

        $file_name = isset($_POST['file_name']) ? addslashes($_POST['file_name']) : $info['file_name'];
        $comment = isset($_POST['comment']) ? addslashes($_POST['comment']) : $info['comment'];
        $model = db();
        $sql = 'update `images` set file_name="' . $file_name . '",comment="' . $comment . '" where id=' . $img_id . ' limit 1';
        $res = $model->update($sql);
        if (!$res) {
            $this->setResult(2, '修改图片信息失败');
        }
        $this->setResult(0, '修改图片信息成功');
    }

    /**
     * AJAX分页获取图片的评论信息
     */
    public function imgComments()
    {
        
    }

    /**
     * 批量图片的评论信息
     */
    public function imgCommentsDelete()
    {
        
    }

    /**
     * 删除图片
     */
    public function delete()
    {
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;

        $model = db();
        $info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
        if (!$info)
            $this->setResult(1, '图片未找到');

        $file_path = $info['file_path'];
        $list = glob($file_path . '_*');
        if (!empty($list)) {
            foreach ($list as $v) {
                @unlink($v);
            }
        }
        unlink($file_path);
        $model->update('delete from images where id=' . $img_id . ' limit 1');
        $this->setResult(0, '删除成功');
    }

    /**
     * 旋转图片
     */
    public function rotate()
    {
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;

        $model = db();
        $info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
        if (!$info) {
            $this->setResult(1, '图片未找到');
        }

        $real_path = $info['file_path'];
        $list = glob($real_path . '_*');
        if (!empty($list)) {
            foreach ($list as $v) {
                @unlink($v);
            }
        }
        if (!is_file($real_path)) {
            $this->setResult(2, '原图片未找到');
        }

        $extension = strtolower(pathinfo($real_path, PATHINFO_EXTENSION));
        if ($extension == 'jpg' || $extension == 'jpeg') {
            $img_str = 'jpeg';
        } else if ($extension == 'png') {
            $img_str = 'png';
        } else if ($extension == 'gif') {
            $img_str = 'gif';
        } else {
            $this->setResult(3, '该文件不能旋转');
        }
        $fun1 = 'imagecreatefrom' . $img_str;
        $fun2 = 'image' . $img_str;
        $source = $fun1($real_path);
        if (!$source) {
            $this->setResult(4, '打开文件失败');
        }
        $rotate = imagerotate($source, -90, 0);
        if (!$rotate) {
            $this->setResult(5, '旋转文件失败');
        }
        $fun2($rotate, $real_path, 100);

        $IMAGE = new \App\Service\Image();
        $IMAGE->changeImgWidth1Height($img_id);

        $this->setResult(0, '旋转成功');
    }

    public function check1repair()
    {
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;

        $model = db();
        $info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
        if (!$info) {
            $this->setResult(1, '图片未找到');
        }

        $real_path = $info['file_path'];
        $list = glob($real_path . '_*');
        if (!empty($list)) {
            foreach ($list as $v)
                @unlink($v);
        }
        if (!is_file($real_path)) {
            $this->setResult(2, '原图片未找到');
        }
        $this->setResult(0, '检查完毕');
    }

    /**
     * 移动到其他相册
     */
    public function move()
    {
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;
        if ($img_id < 1) {
            $this->setResult(1, '请选择照片');
        }
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
        if ($category_id < 1) {
            $this->setResult(2, '请选择相册');
        }
        $model = db();
        //获取相册信息
        $category_info = $model->find('select * from `categories` where id=' . $category_id . ' limit 1');
        if (empty($category_info)) {
            $this->setResult(4, '相册未找到');
        }
        //获取照片信息
        $info = $model->find('select * from `images` where id=' . $img_id . ' limit 1');
        if (!$info) {
            $this->setResult(3, '图片未找到');
        }

        //要移动到的相册与原相册相同
        if ($info['category_id'] == $category_id) {
            $this->setResult(5, '要移动到的相册与原相册相同');
        }

        //删除缩略图
        $real_path = $info['file_path'];
        $list = glob($real_path . '_*');
        if (!empty($list)) {
            foreach ($list as $v) {
                @unlink($v);
            }
        }

        //移动文件
        $file_save_path = config('IMAGE_UPLOAD_DIR') . DIRECTORY_SEPARATOR . $category_info['dir'] . DIRECTORY_SEPARATOR . basename($real_path);
        $move_res = rename($real_path, $file_save_path);
        if ($move_res == false)
            $this->setResult(6, '移动原文件失败');

        //修改数据库
        $update_res = $model->update('UPDATE `images` i,`categories` c SET i.`category_id`=' . $category_id . ',i.`file_path`="' . $file_save_path . '",c.`last_upload_time`="' . date('Y-m-d H:i:s') . '" WHERE i.`id`=1 AND c.`id`=' . $category_id);
        if (!$update_res) {
            rename($file_save_path, $real_path);
            $this->setResult(7, '移动失败');
        }
        $this->setResult(0, '移动成功');
    }

}
