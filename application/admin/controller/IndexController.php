<?php
/**
 * 首页控制器
 * User: v_ywwyang
 * Date: 2018/4/28
 * Time: 15:08
 */
namespace app\admin\controller;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $left_menu = $this->getMenu($this->userInfo['id'],$this->userInfo['uname']);
        $this->assign('userName',$this->userInfo['real_name']);
        $this->assign('left_menu',$left_menu);//get_re($left_menu);die;
        return $this->fetch();
    }

    public function main()
    {
        return $this->fetch();
    }
}
