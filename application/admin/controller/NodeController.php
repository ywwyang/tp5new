<?php
/**
 * 节点控制器
 * User: v_ywwyang
 * Date: 2018/4/28
 * Time: 15:08
 */
namespace app\admin\controller;

use app\admin\model\Node;

class NodeController extends BaseController
{
    protected $node;

    public function __construct()
    {
        parent::__construct();
        $this->node = new Node();
    }

    /**
     * 用户界面
     * @return mixed
     */
    public function index(){
        if($this->request->isAjax()){
            $node_list = $this->node->getTreeList();
            return $node_list;
        }
        return $this->fetch();
    }

    /**
     * 异步获取父节点
     * @return mixed
     */
    public function getNode(){
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->node->getNode($data);
            return $result;
        }
    }

    /**
     * 添加节点
     * @return mixed
     */
    public function addNode(){
        $data = $this->request->param();
        if ($this->request->isPost()) {
            $result = $this->node->addNode($data);
            return $result;
        }
        return $this->fetch();
    }

    /**
     * 更新节点信息
     * @return mixed
     */
    public function editNode(){
        $data = $this->request->param();
        if ($this->request->isPost()) {
            $result = $this->node->editNode($data);
            return $result;
        }

        $node_details = $this->node->getNode_details($data);
        $this->assign('node_details',$node_details);
        return $this->fetch();
    }

    /**
     * 设置状态
     * @return mixed
     */
    public function setStatus()
    {
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->node->setStatus($data);
            return $result;
        }
    }

    /**
     * 删除节点
     * @return int
     */
    public function delNode(){
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $result = $this->node->delNode($data);
            return $result;
        }
    }
}


















