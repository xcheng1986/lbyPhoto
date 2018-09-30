<?php

function getImgUrl($img_id, $size = 0)
{
	$url = '/Image/show?img_id=' . $img_id;
	if ($size) {
        $url .= '&size=' . abs(intval($size));
    }
    return $url;
}

/**
 * 删除目录
 * @param type $path
 */
function delete_dir($path)
{
	$op = dir($path);
	while (false != ($item = $op->read())) {
		if ($item == '.' || $item == '..') {
            continue;
        }

        if (is_dir($op->path . DIRECTORY_SEPARATOR . $item)) {
            delete_dir($op->path . DIRECTORY_SEPARATOR . $item);
        } else {
            unlink($op->path . DIRECTORY_SEPARATOR . $item);
        }
    }
    rmdir($path);
}

/**
 * setResult
 * @param type $code
 * @param type $message
 * @param type $data
 * @param type $ext
 * @return type
 */
function setResult($code, $message = '', $data = null, $ext = null)
{
	$arr = ['status' => $code, 'info' => $message, 'data' => $data];
	if (!is_null($ext)) {
        $arr['ext'] = $ext;
    }
    return $arr;
}

/**
 * uuid
 * @param type $prefix
 * @return type
 */
function uuid($prefix = '')
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid = substr($chars, 0, 8) . '-';
	$uuid .= substr($chars, 8, 4) . '-';
	$uuid .= substr($chars, 12, 4) . '-';
	$uuid .= substr($chars, 16, 4) . '-';
	$uuid .= substr($chars, 20, 12);
	return $prefix . $uuid;
}

/**
 * is_phoneNumber
 * @param type $phone_number
 * @return type
 */
function is_phoneNumber($phone_number)
{
	return preg_match('/^1\d{10}$/', $phone_number) ? true : false;
}

function sendYMZ($phone, $code)
{
	define('KEY_PRIVATE', 'xcheng1986!@#$%^');
	$url = 'http://job.lixiaocheng.com/aliyun_mns/index.php';
	$res = mycurl($url, 'json', 'POST', [
		'phone' => $phone,
		'code' => $code,
		'token' => md5($phone . KEY_PRIVATE . $code)
	]);
	return isset($res['status']) ? ($res['status'] == 0 ? true : false) : FALSE;
}

/**
 * mycurl
 * @param type $url
 * @param type $data_type
 * @param type $method
 * @param type $post_data
 * @param type $header
 * @return type
 */
function mycurl($url = '', $data_type = 'json', $method = 'GET', $post_data = array(), $header = array())
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_ENCODING, ' gzip, deflate');
	if ($method != 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if (!empty($post_data)) {
        curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	if (preg_match('/https/i', $url)) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$cons = curl_exec($ch);
	curl_close($ch);
	if ($data_type == 'json') {
        return json_decode($cons, true);
    }
    return $cons;
}
