<?php
/**
 * 权限管理控制器
 * User: v_ywwyang
 * Date: 2018/5/2
 * Time: 11:45
 */
namespace app\admin\controller;

use app\admin\model\Access;

class AccessController extends BaseController
{
	protected $access;

	public function __construct(){
		parent::__construct();
		$this->access = new Access();
	}

	/**
	 * 页面渲染
	 * @return mixed
	 */
	public function index(){
		$data = $this->request->param();
		// 角色信息
		$accessInfo = $this->access->getAccessInfo($data);
		if(isset($accessInfo['code']) && 400 === $accessInfo['code']){
			$this->error($accessInfo['msg']);
		}

		$this->assign('role', $accessInfo['role']);
		return $this->fetch();
	}

	/**
	 * 添加权限
	 * @return mixed
	 */
	public function addAccess(){
		$data = $this->request->param();
		if ($this->request->isAjax()) {
			//dump($data	);die;
			$roleData = $this->access->addAccess($data);
			return $roleData;
		}
		return returnMsg(400, '请求错误');
	}

	/**
	 * 页面获取节点权限数据
	 * @return mixed
	 */
	public function ajaxGetAccessInfo(){
		// 判断是否为isAjax请求
		if ($this->request->isAjax()) {
			// 接收角色ID
			$data =$this->request->param();
			// 角色信息
			$accessInfo = $this->access->getAccessInfo($data);
			// 已有权限信息
			$hoveInfo = $accessInfo['haveInfo'];

			$allData = $accessInfo['nodeAll'];
			foreach ($allData as $key => &$value) {
				//$allData[$key]['name'] = $value['title'];
				// 去除title的值
				array_splice($allData[$key], 3,1);
				// 已有权限的情况下给与checked
				if(in_array($value['id'], $hoveInfo)){
					$value['checked']= 'true';
					$allData[$key]['open'] = 'true';
				}
			}//get_re($allData);
			return $allData;
		}
	}
}

