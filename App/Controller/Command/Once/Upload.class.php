<?php

namespace App\Controller\Command\Once;

use App\Service\Image;
use App\Service\Category;
use App\Service\Oss;

/**
 * 将现有的图片上传值阿里云OSS
 *
 * @author lxc
 */
class Upload
{

    /**
     * uploadImage
     * @command php Public\cli.php  "/command/Once/Upload/run"
     */
    public function run()
    {
        $Image = new Image();
        //获取所有的图片
        $all_images = $Image->all();
        if (empty($all_images)) {
            die("获取图片为空");
        }

        //获取所有的分类信息
        $category_infos = (new Category())->all();
        if (empty($category_infos)) {
            die("获取图片分类信息为空");
        }

        foreach ($all_images as $v) {
            echo "run {$v['id']} " . PHP_EOL;
            $category_info_dir = isset($category_infos[$v['category_id']]) ? $category_infos[$v['category_id']]['dir'] : '';
            if ($category_info_dir == '') {
                echo "[error] category_id:{$v['category_id']} is not found" . PHP_EOL;
            }

            if (strtolower(php_uname('s')) != 'linux') {
                $v['file_path'] = str_replace('/www/liboyang', ROOT_PATH, $v['file_path']);
                $v['file_path'] = str_replace('/', '\\', $v['file_path']);
            }

            $err = '';
            $res = (new Oss())->uploadFile($category_info_dir, $v['file_path'], 'img', $err);
            if (!$res) {
                die($err ?: 'upload undefined error');
            }
            $url_new = isset($res['oss-request-url']) ? $res['oss-request-url'] : '';
            if ($url_new == '') {
                die(print_r($res, true) . PHP_EOL . "upload error");
            }

            //update mysql
            $update_res = $Image->update($v['id'], ['url' => $url_new], $err);
            if ($update_res == false) {
                die($err ?: 'update undefined error');
            }
        }
    }

}
