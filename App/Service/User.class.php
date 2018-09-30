<?php

namespace App\Service;

/**
 * Description of User
 *
 * @author Administrator
 */
class User
{

	/**
	 *
	 * @param type $phone_number
	 * @param type $passwd
	 */
	public function getUserByPhoneAndPass($phone_number, $passwd)
	{
		$model = db();
		$passwd_new = md5(base64_encode($passwd) . config('APP_KEY'));
		$user_info = $model->find('SELECT * FROM `users` WHERE phone_number="' . addslashes($phone_number) . '" AND `password`="' . $passwd_new . '" LIMIT 1');
		return $user_info ?: [];
	}

	public function updateUserLogin($uid, $ip_string = null, $date_time = null)
	{
		if (is_null($ip_string))
			$ip_string = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		if (is_null($date_time))
			$date_time = date('Y-m-d H:i:s');
		$model = db();
		$res = $model->update('update `users` set `last_visit_ip`="' . addslashes($ip_string) . '",`last_visit_time`="' . addslashes($date_time) . '" where id= ' . $uid . ' limit 1');
		return $res ? true : false;
	}
	
	public function addUser($phone_number, $passwd){
		$model = db();
		$now = date('Y-m-d H:i:s');
		$R = [
			'phone_number'=>$phone_number,
			'nikename'=>$phone_number,
			'password'=>md5(base64_encode($passwd) . config('APP_KEY')),
			'email'=>'',
			'status'=>'normal',
			'last_visit_ip'=>'',
			'last_visit_time'=>'',
			'create_time'=>$now,
			'update_time'=>$now,
		];
		$sql = 'INSERT INTO `users` SET phone_number="'.$phone_number.'" ,nikename="'.$phone_number.'",`password`="'.$R['password'].'",`status`="normal",create_time="'.$now.'",update_time="'.$now.'"';
		$id = $model->update($sql);
		if(!$id)
			return false;
		$R['id'] = $id;
		return $R;
	}
	
	public function phone_number_isused($phone_number){
		$model = db();
		return $model->find('select * from `user` where phone_number="'.$phone_number.'" limit 1')?:[];
	}

}
