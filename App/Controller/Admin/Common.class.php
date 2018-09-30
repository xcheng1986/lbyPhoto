<?php

namespace App\Controller\Admin;

/**
 * Description of Common
 *
 * @author Administrator
 */
class Common extends \App\Controller\Common
{

    public function __construct()
    {
        $user = isset($_SESSION['user']) ? $_SESSION['user'] : [];
        if (empty($user)) {
            $this->error('请登录', '/Admin/Login/index');
        }
    }

}
