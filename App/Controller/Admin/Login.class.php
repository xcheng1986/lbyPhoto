<?php

namespace App\Controller\Admin;

/**
 * Description of Login
 *
 * @author Administrator
 */
class Login extends \App\Controller\Common
{

    public function __construct()
    {
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
        if (!empty($user)) {
            $this->success('您已经登录', '/Admin/Index/index');
        }
    }

    public function index()
    {
        $this->display('Admin/Login/login');
    }

    public function todo()
    {
        $phone_number = isset($_POST['phone']) ? $_POST['phone'] : '';
        if (\is_phoneNumber($phone_number) == false) {
            $this->setResult(2, '手机号为空或格式错误');
        }

        $passwd = isset($_POST['passwd']) ? htmlentities($_POST['passwd']) : '';
        if ($passwd == '') {
            $this->error('登录密码为空或错误');
        }

        $USER = new \App\Service\User();
        $user_info = $USER->getUserByPhoneAndPass($phone_number, $passwd);
        if (empty($user_info)) {
            $this->error('账号或密码错误');
        }

        $_SESSION['user'] = $user_info;
        $USER->updateUserLogin($user_info['id']);
        $this->success('登录成功', '/admin');
    }

    /**
     * 
     */
    public function send_code()
    {
        $this->error('短信发送功能已经关闭');
        exit(0);
        $phone_number = isset($_POST['phone']) ? $_POST['phone'] : '';
        if (\is_phoneNumber($phone_number) == false) {
            $this->setResult(2, '手机号为空或格式错误');
        }
        $cache = new \Core\Lib\Cache();
        $now_time_zone = time();
        $cache_time = 5 * 60;

        $type = isset($_POST['type']) ? $_POST['type'] : 'login_verify';

        //获取上次发送短信的时间
        $time_old = $cache->get($type . '_time_' . $phone_number);
        if ($time_old && ($now_time_zone - (int) $time_old) < 60) {
            $this->setResult(3, '短信发送太频繁');
        }

        $code = mt_rand(100000, 999999);
        $cache->set($type . '_code_' . $phone_number, $code, $cache_time);

        $res = sendYMZ($phone_number, $code);
        \Core\Lib\Log::write('验证码短信发送' . $phone_number . ':' . $code, 'RUN_INFO');
        if (!$res) {
            $this->setResult(3, '手机短信验证码发送失败');
        } else {
            $cache->set($type . '_time_' . $phone_number, $now_time_zone, $cache_time);
            $this->setResult(0, '手机短信验证码发送成功', $res);
        }
    }

    public function regist()
    {
        $this->error('注册已经关闭');
        exit(0);
        $this->display('Admin/Login/regist');
    }

    public function toReg()
    {
        $this->error('注册已经关闭');
        exit(0);
        $phone_number = isset($_POST['phone']) ? $_POST['phone'] : '';
        if (\is_phoneNumber($phone_number) == false) {
            $this->setResult(2, '手机号为空或格式错误');
        }

        $passwd = isset($_POST['passwd']) ? htmlentities($_POST['passwd']) : '';
        if ($passwd == '') {
            $this->error('登录密码为空或错误');
        }

        $verify_code = isset($_POST['verify_code']) ? $_POST['verify_code'] : '';
        if ($verify_code == '') {
            $this->error('手机验证码为空');
        }
        $cache = new \Core\Lib\Cache();
        $verify_code_cache = $cache->get('regist_verify_code_' . $phone_number);
        if ($verify_code_cache == '') {
            $this->error('手机未短信验证');
        }
        if ($verify_code_cache != $verify_code) {
            $this->error('手机短信错误');
        }

        $USER = new \App\Service\User();
        $phone_number_isused = $USER->phone_number_isused($phone_number);
        if ($phone_number_isused) {
            $this->error('该手机号已经注册过');
        }

        $user_info = $USER->addUser($phone_number, $passwd);
        if (!$user_info) {
            $this->error('用户注册失败');
        }

        $_SESSION['user'] = $user_info;
        $USER->updateUserLogin($user_info['id']);
        $this->success('用户注册成功', '/admin/index.html');
    }

    public function getPass()
    {
        $this->error('功能暂未开放');
    }

}
