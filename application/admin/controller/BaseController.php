<?php
/**
 * 顶级控制器
 * User: v_ywwyang
 * Date: 2018/4/28
 * Time: 15:08
 */
namespace app\admin\controller;

use app\admin\model\User;
use org\Rbac;
use think\Controller;
use think\Db;

class BaseController extends Controller
{
    public $userInfo;

    public function __construct()
    {
        parent::__construct();
        parent::_initialize();
        // 每次来这先判断是否有session
        $session_key = checkLogin();
        if(!$session_key){
            $this->redirect(url("Login/index"));
        }

        //$session_key  = 1;
        // 获取用户信息
        $userModel = new User();
        $this->userInfo= $userModel->getUserInfo($session_key);//get_re($this->userInfo);

        //权限验证
        if($this->userInfo['uname'] != config('rbac.rbac_superadmin')){
            if (!Rbac::AccessDecision($this->request->module())) {
                $this->error('暂无访问权限');
            }
        }
    }

    /**
     * 返回后台菜单
     * @return array
     */
    protected function getMenu($userId,$userName){
        //获取权限表
        $table = array(
            'role'   => config('rbac.rbac_role_table'),
            'user'   => config('rbac.rbac_user_table'),
            'access' => config('rbac.rbac_access_table'),
            'node'   => config('rbac.rbac_node_table')
        );

        //查询条件
        $where = array(
            'u.user_id' => $userId,
            'r.status'  => 1,
            'n.status'  => 1,
        );

        //获取菜单
        $accessMeunList = Db::name('menu')
            ->where(['status' => array('eq', 1)])
            ->order('sort desc')
            ->select();

        //获取权限
        $menuList = Db::name($table['user'] . ' u')
            ->field('n.*')
            ->join($table['role'] . ' r', 'u.role_id = r.id ')
            ->join($table['access'] . ' a', 'a.role_id = r.id ')
            ->join($table['node'] . ' n', 'a.node_id = n.id ')
            ->where($where)
            ->order('sort desc')
            ->select();

        // 超级管理员显示全部菜单
        if(config('rbac.rbac_superadmin') === $userName){
            $menuList = '*';
        }

        //获取菜单
        $access_list = get_access($menuList);
        $result = get_menu($access_list, $accessMeunList);
        return json_decode( json_encode( $result ),true);
    }
}
