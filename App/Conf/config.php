<?php

$user_setting = [];
if (file_exists(__DIR__ . '/user_setting.conf.php')) {
    $user_setting = include __DIR__ . '/user_setting.conf.php';
}

return array_merge([
    'MYSQL_OPTION' =>
    php_uname('n') == 'dev' ?
    [
        'host' => $_SERVER['MYSQL_LOCAL_HOST'],
        'user' => $_SERVER['MYSQL_LOCAL_USER'],
        'password' => $_SERVER['MYSQL_LOCAL_PASS'],
        'database' => $_SERVER['MYSQL_LOCAL_DATABASE_PHOTO'],
        'port' => $_SERVER['MYSQL_LOCAL_PORT'],
        'socket' => '',
    ] : 
    [
        'host' => $_SERVER['MYSQL_REMOTE_HOST'],
        'user' => $_SERVER['MYSQL_REMOTE_USER'],
        'password' => $_SERVER['MYSQL_REMOTE_PASS'],
        'database' => $_SERVER['MYSQL_REMOTE_DATABASE_PHOTO'],
        'port' => $_SERVER['MYSQL_REMOTE_PORT'],
        'socket' => '',
    ],
    'STATIC_SERVER_IP' => php_uname('n') == 'dev' ? '127.0.0.1' : '127.0.0.1',
    'STATIC_SERVER_HOST' => 'static.lixiaocheng.com',
    'SESSION_EXPIRE' => 3600 * 24 * 7, //登录缓存一周
    'SESSION_TABLE' => 'session',
    'MEMCACHE_HOST' => $_SERVER['CFG_MEMCACHE_HOST'],
    'MEMCACHE_PORT' => $_SERVER['CFG_MEMCACHE_PORT'],
    'DATA_CACHE_TIMEOUT' => 0,
    'DATA_CACHE_TIME' => 0,
    'DATA_CACHE_PREFIX' => $_SERVER['CFG_PHPTO_DATA_CACHE_PREFIX'],
    'APP_KEY' => $_SERVER['CFG_PHPTO_APP_KEY'],
    #
    'LOG_FILE_DIR' => $_SERVER['CFG_PHPTO_RUNTIME_DIR'],
    #
    'ERROR_JUMP_TEMPLATE' => APP_PATH . '/View/Pub/error_jump.html',
    'SUCCESS_JUMP_TEMPLATE' => APP_PATH . '/View/Pub/success_jump.html',
    'SQL_LOG' => true, //sql日志是否开启
    'IMAGE_UPLOAD_DIR' => ROOT_PATH . '/Public/uploads', //文件上传目录
    'IMAGE_ORDER_KEYS' => [
        1 => ['name' => '上传时间倒序', 'key' => 'update_time desc'],
        2 => ['name' => '上传时间正序', 'key' => 'update_time asc'],
        3 => ['name' => '文件名倒序', 'key' => 'file_name desc'],
        4 => ['name' => '文件名正序', 'key' => 'file_name asc'],
        5 => ['name' => '评分倒序', 'key' => 'score desc'],
        6 => ['name' => '评分正序', 'key' => 'score asc'],
        7 => ['name' => '拍摄时间倒序', 'key' => 'datetime_original desc'],
        8 => ['name' => '拍摄时间正序', 'key' => 'datetime_original asc']
    ],
    //阿里云oss配置
    'ALIYUN_OSS' => [
        'AccessKeyID' => $_SERVER['CFG_PHPTO_OSS_ACCESSKEYID'],
        'AccessKeySecret' => $_SERVER['CFG_PHPTO_OSS_ACCESSKEYSECRET'],
        'Bucket' => $_SERVER['CFG_PHPTO_OSS_BUCKET'],
        'EndPoint' => $_SERVER['CFG_PHPTO_OSS_ENDPOINT'],
    ],
], $user_setting);



