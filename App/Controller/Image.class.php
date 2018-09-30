<?php

namespace App\Controller;

/**
 * Description of Image
 *
 * @author Administrator
 */
class Image extends \App\Controller\Common
{

    public function __contruct()
    {
        parent::__contruct();
    }

    /**
     * 显示图片
     * @url /Image/show?img_id=1&size=500
     */
    public function show()
    {
        $size = isset($_GET['size']) ? intval($_GET['size']) : 0;
        $img_id = isset($_GET['img_id']) ? intval($_GET['img_id']) : 0;

        if ($img_id) {
            $model = db();
            $info = $model->find('SELECT * FROM `images` WHERE id=' . $img_id . ' limit 1');
            $this->show_image($info, $size);
        } else {
            $this->show_image([], $size);
        }
    }

    private function show_image($file_info, $size)
    {
        $file_path = '';
        if (empty($file_info)) {
            $file_path = ROOT_PATH . '/Public/img/no_photo.jpg';
        } else {
            $file_path = $file_info['file_path'];
            if (!is_file($file_path)) {
                $file_path = ROOT_PATH . '/Public/img/no_photo.jpg';
            }
        }

        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($size) {
            $file_path = $file_path . '_' . $size . '.' . $extension;
        }

        if (!is_file($file_path)) {
            $this->reSizeImage($file_info['file_path'], $size, $file_path);
        }

        $cache_time = 2592000; //30天
        $Etag = md5_file($file_path);
        $filemtime = filemtime($file_path);

        //请求是否已经缓存
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $Etag) {
            header("HTTP/1.1 304 Not Modified");
            header('ETag: "' . $Etag . '"');
            header('Date: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
            header('Cache-Control: max-age=' . $cache_time);
            exit();
        }

        header('Content-Type: image/jpeg');
        header('Transfer-Encoding: chunked');
        header('Cache-Control: max-age=' . $cache_time);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
        header("Etag:" . $Etag);

        echo file_get_contents($file_path);
        exit(0);
    }

    /**
     * reSizeImage
     * @param type $file_path
     * @param type $size
     */
    private function reSizeImage($img_file_path, $size, $new_file_path)
    {
        $imagick = new \Imagick($img_file_path);
        $imagick->thumbnailImage($size, $size, true);
        return $imagick->writeImage($new_file_path);
    }

}
