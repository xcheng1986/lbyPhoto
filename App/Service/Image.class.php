<?php

namespace App\Service;

use Core\Lib\Log;

/**
 * Description of Image
 *
 * @author Administrator
 */
class Image extends \App\Controller\Common
{

    public function table_images()
    {
        return 'images';
    }

    /**
     * 通过接口上图片
     * @param type $file
     */
    public function upload_file_new($file)
    {
        $url = 'http://' . config('STATIC_SERVER_IP') . '/imageUpload';
        $res = mycurl($url, 'json', 'POST', [
            'file' => new \CURLFile($file['tmp_name'], $file['type'], $file['name'])
            ], ['Host: static.lixiaocheng.com']);
        return $res;
    }

    /**
     * update
     * @param type $id
     * @param array $data
     * @param type $err
     * @return boolean
     */
    public function update($id, array $data, &$err = '')
    {
        if(!is_array($data) || empty($data)){
            $err = 'param error';
            return false;
        }

        $info = $this->info($id);
        if (empty($info)) {
            $err = 'data not found';
            return false;
        }

        $update_str = [];
        foreach ($data as $k => $v) {
            $update_str[] = $k . '="' . $v . '"';
        }
        $sql = 'update ' . $this->table_images() . ' set ' . implode(',', $update_str) . ' where id=' . $id;
        try {
            $model = db();
            $res = $model->update($sql);
        } catch (\Exception $ex) {
            $err = $ex->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 上传到本地
     * @param type $file
     * @param type $save_dir
     * @return type
     */
    public function upload_img_to_local($file, $save_dir)
    {
        if ($file['type'] == '' || $file['tmp_name'] == '' || $file['error'] != 0) {
            Log::write(print_r(func_get_args(), true), 'IMAGE_UPLOAD_ERR');
            return setResult(31, '系统上传失败');
        }

        list($file_type, $extension) = explode('/', isset($file['type']) ? ($file['type'] == '' ? '/' : $file['type']) : '/');
        if ($file_type != 'image')
            return setResult(32, '非图片文件不能上传');
        if ($extension == 'jpeg')
            $extension = 'jpg';
        $file_md5 = md5_file($file['tmp_name']);
        $is_uploaded = $this->is_uploaded($file_md5);
        if ($is_uploaded)
            return setResult(33, '该文件已经上传过', $is_uploaded['id']);

        $file_save_path = config('IMAGE_UPLOAD_DIR') . DIRECTORY_SEPARATOR . $save_dir . DIRECTORY_SEPARATOR . $file['name'];
        if (is_file($file_save_path))
            $file_save_path = dirname($file_save_path) . DIRECTORY_SEPARATOR . $file_md5 . '.' . $extension;

        $up_res = $this->upload_file($file, $file_save_path);
        if ($up_res[0] != 0)
            return setResult($up_res[0], $up_res[1]);

        //获取图片exif信息
        $image_type = exif_imagetype($file_save_path);
        if ($image_type == 2) {
            $exif = exif_read_data($file_save_path);
        } else {
            $info = getimagesize($file_save_path);
            $exif = ['DateTimeOriginal' => '', 'FileSize' => $_FILES['file']['size'], 'COMPUTED' => ['Width' => $info[0], 'Height' => $info[1]]];
        }

        return setResult(0, '上传成功', [
            'file_path' => $file_save_path,
            'file_name' => $file['name'],
            'datetime_original' => isset($exif['DateTimeOriginal']) ? $exif['DateTimeOriginal'] : '',
            'filesize' => $exif['FileSize'],
            'width' => $exif['COMPUTED']['Width'],
            'height' => $exif['COMPUTED']['Height'],
            'exif_info' => json_encode($exif),
            'md5_file' => $file_md5,
        ]);
    }

    /**
     * 通过文件的MD5值判断文件是否上传过
     * @param type $file_md5
     * @return type
     */
    private function is_uploaded($file_md5)
    {
        $model = db();
        $img_info = $model->find('SELECT * FROM `images` WHERE md5_file="' . $file_md5 . '" limit 1');
        return $img_info ?: [];
    }

    public function lists()
    {
        
    }

    /**
     * 获取所有的图片信息
     * @return type
     */
    public function all()
    {
        $model = db();
        $all = $model->select('SELECT * FROM `images` WHERE 1');
        return array_column($all, null, 'id');
    }

    /**
     * 获取相册封面URL
     * @param type $image_id
     * @return type
     */
    public function get_category_cover_img_url($image_id = 0)
    {
        return getImgUrl($image_id, 90);
    }

    /**
     *
     * @param type $file_path
     * @param type $file_name
     * @param type $datetime_original
     * @param type $author
     * @param type $filesize
     * @param type $width
     * @param type $height
     * @param type $category_id
     * @param type $exif_info
     * @param type $md5_file
     * @return type
     */
    public function add($file_path, $file_name, $datetime_original, $author, $filesize, $width, $height, $category_id, $exif_info, $md5_file)
    {
        $model = db();
        $now = date('Y-m-d H:i:s');
        $sql = 'insert into ' . $this->table_images() . '(file_path,file_name,datetime_original,author,filesize,width,height,category_id,exif_info,md5_file,create_time) values ('
            . '"' . $file_path . '","' . $file_name . '","' . $datetime_original . '",' . $author . ',' . $filesize . ',' . $width . ',' . $height . ',' . $category_id . ',"' . addslashes($exif_info) . '","' . $md5_file . '","' . $now . '")';
        $res = $model->insert_one($sql);
        return $res ?: false;
    }

    /**
     *
     * @param type $_file
     * @param string $filePath
     * @return type
     */
    private function upload_file($_file, $filePath)
    {
        if (empty($_file))
            return [10, 'Failed to uploaded file'];

        $save_path = dirname($filePath);
        if (!is_dir($save_path) && mkdir($save_path, 0777, true) == false)
            return [12, 'Failed to create save_path'];

        if (!is_dir($save_path) || !$dir = opendir($save_path))
            return [13, 'Failed to open temp directory'];

        if ($_file["error"]) {
            switch ($_file["error"]) {
                case 1:
                    return [15, '上传的文件超限[server]'];
                case 2:
                    return [15, '上传的文件超限[clint]'];
                case 3:
                    return [15, '文件只有部分被上传'];
                case 4:
                    return [15, '没有文件被上传'];
                case 6:
                    return [15, '找不到临时文件夹'];
                case 7:
                    return [15, '文件写入失败'];
                default :
                    return [15, '上传出错'];
            }
        }

        if (!is_uploaded_file($_file["tmp_name"]))
            return [15, 'is not a uploaded file'];

        if (move_uploaded_file($_file['tmp_name'], $filePath) == false)
            return [16, 'Failed to move uploaded file'];
        else
            return [0, 'ok'];
    }

    /**
     * 获取图片信息
     * @param type $img_id
     * @return type
     */
    public function info($img_id)
    {
        $model = db();
        $res = $model->find('select * from images where id=' . $img_id . ' limit 1');
        return $res ?: [];
    }

    /**
     * 更改图片的宽高
     * @param type $img_id
     * @return type
     */
    public function changeImgWidth1Height($img_id)
    {
        $info = $this->info($img_id);
        $model = db();
        $sql = 'update images set width=' . $info['height'] . ',height=' . $info['width'] . ',update_time="' . date('Y-m-d H:i:s') . '" where id=' . $img_id . ' limit 1';
        return $model->update($sql) ? true : false;
    }

    /**
     * 生成缩略图
     * @desc 按比例在安全框 $width*$height 内缩放图片, 输出缩放后图像大小 不完全等于 $width*$height
     */
    public function resize_to($img_file_path, $width, $height, $new_file_path)
    {
        $imagick = new \Imagick($img_file_path);
        $imagick->thumbnailImage($width, $height, true);
        return $imagick->writeImage($new_file_path);
    }

    /**
     * 获取相册最后上传的照片ID(相册封面使用)
     * @param type $category_id
     */
    public function get_category_last_img_id($category_id)
    {
        $model = db();
        $res = $model->find('select id from images where category_id=' . $category_id . ' order by create_time desc limit 1');
        return $res ? ((int) $res['id']) : 0;
    }

}
