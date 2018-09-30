<?php

namespace App\Service;

use OSS\OssClient;
use OSS\Core\OssException;
use Core\Lib\Log;

/**
 * Description of Oss
 *
 * @author lxc
 */
class Oss extends \App\Controller\Common
{

    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $bucket;

    public function __construct()
    {
        $oss_conf = config('ALIYUN_OSS');
        $this->accessKeyId = $oss_conf['AccessKeyID'];
        $this->accessKeySecret = $oss_conf['AccessKeySecret'];
        $this->endpoint = $oss_conf['EndPoint'];
        $this->bucket = $oss_conf['Bucket'];
    }

    /**
     * 上传文件到OSS
     * @param string $category_name
     * @param type $file_real_path
     * @param type $file_type
     * @return type
     */
    public function uploadFile($category_name, $file_real_path, $file_type = 'img', &$err = '')
    {
        if (!is_file($file_real_path)) {
            $err = 'file ' . $file_real_path . ' not exists ';
            return [];
        }
        $category_name = $file_type . '_' . $category_name;
        $object = $category_name . '/' . basename($file_real_path);
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $ossClient->createObjectDir($this->bucket, rtrim($category_name, '/'));
            $res = $ossClient->uploadFile($this->bucket, $object, $file_real_path);
            return $res;
        } catch (OssException $e) {
            $err = $e->getMessage();
            Log::error($err);
            return [];
        }
    }

}
