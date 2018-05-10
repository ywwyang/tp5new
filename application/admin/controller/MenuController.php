<?php
/**
 * 后台菜单管理控制器
 * User: v_ywwyang
 * Date: 2018/4/28
 * Time: 9:14
 */
namespace app\admin\controller;

use  app\admin\model\Menu;

class MenuController extends BaseController
{
    public $menu;

    public function __construct()
    {
        parent::__construct();
        $this->menu = new Menu();
    }

    /**
     * 列表页显示
     * @return mixed
     */
    public function index(){
        return $this->fetch();
    }

    /**
     * 获取菜单列表
     * @return mixed
     */
    public function getMenuList(){
        $result = $this->menu->getMenuList();
        return  $result;
    }

    /**
     * 获取父级菜单
     * @return mixed
     */
    public function getParent(){
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->menu->getParents($data);
            return $result;
        }
    }

    /**
     * 添加菜单
     * @return mixed
     */
    public function addMenu(){
        $data = $this->request->param();
        if ($this->request->isPost()) {
            $result = $this->menu->addMenu($data);
            return $result;
        }
        $levels = $this->menu->get_menu_level();
        $this->assign('levels',$levels);

        return $this->fetch();
    }

    /**
     * 获取节点
     * @return mixed
     */
    public function getNodeList()
    {
        $data = $this->request->param();
        if($this->request->isAjax()){
            $result = $this->menu->getNodeList($data);
            return $result;
        }
    }

    /**
     * 编辑菜单
     * @return mixed
     */
    public function editMenu(){
        $data = $this->request->param();
        if ($this->request->isPost()) {
            $result = $this->menu->editMenu($data);
            return $result;
        }
        //获取菜单（转化成对应的节点）
        $menu = $this->menu->get_edit_menu($data);
        $this->assign('menu',$menu);//get_re($menu);die;

        //父级
        $levels = $this->menu->get_menu_level();
        $this->assign('levels',$levels);

        //获取节点（模块、控制器、方法）
//        $node = $this->menu->get_menu_node();
//        $this->assign('node',$node);
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
            $result = $this->menu->setStatus($data);
            return $result;
        }
    }

    /**
     * 删除菜单
     * @return [type] [description]
     */
    public function delMenu(){
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            $result = $this->menu->delMenu($data);
            return $result;
        }
    }

}



















