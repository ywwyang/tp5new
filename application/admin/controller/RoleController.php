<?php
/**
 * 角色控制器
 * User: v_ywwyang
 * Date: 2018/5/2
 * Time: 11:45
 */
namespace app\admin\controller;


use app\admin\model\Role;

class RoleController extends BaseController
{
	protected  $role;
	// 初始化
	public function __construct()
	{
		parent::__construct();
		$this->role = new Role();
	}

	/**
	 * 列表页渲染
	 * @return mixed
	 */
	public function index(){
		// 获取角色信息
		$roleData = $this->role->getParentInfo();
		// 生成递归树
		$groupData = $this->roleRecursion($roleData['result'],0);
		$this->assign('roleData',$groupData);
		// 分页显示输出
		$page = $roleData['result']->render();
		$this->assign('page',$page);
		return $this->fetch();
	}

	/**
	 * 添加角色保存
	 * @return mixed
	 */
	public function addRole(){
		$data = $this->request->param();
		if($this->request->isPost()){
			$result = $this->role->addRole($data);
			return $result;
		}

		$roleData = $this->role->getAddInfo();
		if ($roleData['code'] != 400  && isset($roleData['data'])) {
			$this->assign('roleData',$roleData['data']);
		}
		return $this->fetch();
	}

	/**
	 * 修改角色保存
	 * @return mixed
	 */
	public function editRole(){
		$data = $this->request->param();

		if($this->request->isPost()){
			$result = $this->role->editRole($data);
			return $result;
		}

		$roleData = $this->role->getParentInfo($data['id']);
		$this->assign('parentName',$roleData['parentName']);
		$this->assign('roleData',$roleData['result']);
		return $this->fetch();
	}

	/**
	 * 删除角色
	 * @return mixed
	 */
	public function delRole(){
		$data = $this->request->param();
		if($this->request->isPost()){
			$result =$this->role->delRole($data);
			return $result;
		}
	}

	/**
	 * 设置角色状态
	 * @return mixed
	 */
	public function setStatus(){
		$data = $this->request->param();
		if($this->request->isPost()){
			$result = $this->role->setStatus($data);
			return $result;
		}
	}

	/**
	 * 树形菜单格式
	 * @param  [type]  $info 
	 * @param  integer $pid  父级ID(顶级)
	 * @param  integer $lev  [description]
	 * @return [type]        数组
	 * @author 稻香
	 */
	protected function roleRecursion($info,$pid = 0,$lev = 0){
		$html = [];
		foreach ($info as $val) {
			if ($val['pid'] == $pid) {
				$roleId = $val['id'];
				$html[$val['id']]['id'] = $val['id'];
				$html[$val['id']]['name'] = str_repeat("&nbsp;&nbsp;",$lev) . '└─' . $val['name'];
				$html[$val['id']]['pid'] = $val['pid'];
                $html[$val['id']]['status'] = $val['status'];
				$html[$val['id']]['remark'] = $val['remark'];
				$html[$val['id']]['pid_name'] = $val['pid_name'];
                $html = $html + $this->roleRecursion($info, $roleId, $lev+2,$html);
			}
		}
		return $html;
	}
}
