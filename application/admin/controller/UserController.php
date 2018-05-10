<?php
/**
 * 用户控制器
 * User: v_ywwyang
 * Date: 2017/10/26
 * Time: 11:49
 */
namespace app\admin\controller;

use app\admin\model\User;
use think\Db;

class UserController extends BaseController
{
    protected $user;
    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    /**
     * 列表展示
     * @return mixed
     */
    public function index(){
        //$this->user->test();die;
        $Data = $this->user->getParentInfo();
        $this->assign('role',$Data['role']);
        $this->assign('userList',$Data['user']);
        // 分页显示输出
        $page = $Data['user']->render();
        $this->assign('page',$page);
        // 模板赋值
        $this->assign('admin_name',config('rbac.admin_auth_key'));
        return $this->fetch();
    }

    /**
     * 添加用户
     * @return bool|false|int|mixed
     */
    public function addUser(){
        $data = $this->request->param();
        if ($this->request->isPost()) {
            //$data['last_login_ip'] = $this->request->ip();
            $data['last_login_time'] = time();
            $data['createtime'] = time();
            $result = $this->user->addUser($data);
            return $result;
        }
        return $this->fetch();
    }

    /**
     * 修改用户信息
     * @return $this|bool|mixed
     */
    public function editUser(){
        $data = $this->request->param();
        if ($this->request->isPost()) {
            if ($data['newPwd'] !== $data['password']) {
                return returnMsg(400, '确认密码有误，请重新输入');
            }
            $data['last_login_time'] = time();
            $result = $this->user->editUser($data);
            return $result;
        }

        $user = $this->user->getUser($data);
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 设置状态
     * @return mixed
     */
    public function setStatus(){
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->user->setStatus($data);
            return $result;
        }
    }

    /**
     * 删除用户
     * @return mixed
     */
    public function delUser(){
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->user->delUser($data);
            return $result;
        }
    }

    /**
     * 分配角色界面渲染
     * @return mixed
     */
    public function assignRole(){
        $data = $this->request->param();
        $result = $this->user->getRoleInfo($data['id']);
        $userInfo = $this->user->getUserInfo($data['id']);
        $this->assign('roleInfo',$result[0]);
        $this->assign('currRole',$result[1]);
        $this->assign('userInfo',$userInfo);
        return $this->fetch('assignRole');
    }

    /**
     * 分配角色保存
     * @return mixed
     */
    public function addUserRole(){
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->user->AddUserRole($data);
            return $result;
        }
    }
}