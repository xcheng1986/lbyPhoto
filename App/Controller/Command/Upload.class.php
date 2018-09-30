<?php

namespace App\Controller\Command;

use Core\Lib\Command;
use App\Service\Oss;

/**
 * Description of Upload
 *
 * @author lxc
 */
class Upload extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * uploadImage
     * @command php Public\cli.php  "/command/Upload/uploadImage"  "file=123&category=1"
     */
    public function uploadImage($param)
    {
        if (!isset($param['file']) || !is_file($param['file'])) {
            die("文件不存在");
        }

        $category_id = isset($param['category']) ? intval($param['category']) : 0;
        if ($category_id < 1) {
            die("请选择相册");
        }

        $CATEGORY = new \App\Service\Category();
        $category_info = $CATEGORY->info($category_id);
        if (empty($category_info)) {
            die("相册未找到");
        }

        $res = (new Oss())->uploadFile($category_info['dir'], realpath($param['file']), 'img');
        print_r($res);
    }

    /**
     * uploadVedio
     */
    public function uploadVideo($param)
    {
        
    }

}
