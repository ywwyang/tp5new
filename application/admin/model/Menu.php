<?php
/**
 * 后台菜单管理模型
 * User: v_ywwyang
 * Date: 2018/4/28
 * Time: 9:31
 */
namespace app\admin\model;

use think\Model;
use think\Validate;

class Menu extends Model
{
    protected $table = 'menu';

    public $rule = [
        'add' => [
            'title'   => 'require',
            'status' => 'require',
            'sort'   => 'require|number',
            'pid' => 'require|number',
            'level' => 'require|number',
        ],
        'update' => [
            'id' => 'require|integer',
            'title'   => 'require',
            'status' => 'require',
            'sort'   => 'number',
            'pid' => 'require|number',
            'level' => 'require|number',
        ],
        'del' => [
            'id' => 'require|integer',
        ],
        'get_parent'=>[
            'level' => 'require|number',
        ],
        'getNodes'=>[
            'level' => 'require|number',
            'pid' => 'require|integer',
        ],
        'set_status'=>[
            'id' => 'require|integer',
            'status' => 'require|integer',
        ]
    ];

    public $msg = [
        'title.require' => '标题必须',
        'status.require' => '状态必须',
        'sort.number' => '排序需为数字',
        'sort.require' => '排序必须',
        'pid.require' => '父级必须',
        'pid.number' => '参数错误，联系管理员！',
        'level.number' => '参数错误，联系管理员！',
        'level.require' => '用户ID失效',
        'id.require' => '用户参数错误',
        'id.integer' => '参数错误，联系管理员！',
        'status.integer' => '参数错误，联系管理员！',
        'url_action.require' => '方法不能为空',
        'url_action.alpha' => '方法只能是字母',
        'url_controller.require' => '控制器不能为空',
        'url_controller.alpha' => '控制器只能是字母',
        'url_module.require' => '模块不能为空',
        'url_module.alpha' => '模块只能是字母',
    ];

    /**
     * 列表页数据获取
     * @return mixed
     */
    public function getMenuList(){
        $menu_list =  $this->field('id,pid as pId,title as name,status,remark as title')->where([])->select();

        foreach($menu_list as $key=>$val){
            if(1 == $val['status']){
                $menu_list[$key]['icon'] = config('tree.yes');
            }else{
                $menu_list[$key]['icon'] = config('tree.no');
            }
        }
        return $menu_list;
    }

    /**
     * 获取菜单类别
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function get_menu_level(){
        $result = $this->field('level')->group('level')->select();
        return $result;
    }

    /**
     * 获取节点
     * @return mixed
     */
    public function getNodeList($data){
        $validate = new Validate($this->rule['getNodes'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return  returnMsg(400, $validate->getError());
        }

        $nodeList = Node::all(['level' => intval($data['level']),'pid' => intval($data['pid']),'status' => array('neq',0)]);
        if(!$result){
            return returnMsg('400','获取节点失败！');
        }
        return returnMsg('200','获取节点成功！',$nodeList);
    }

    /**
     * 获取父级菜单
     * @return mixed
     */
    public function getParents($data){
        $validate = new Validate($this->rule['get_parent'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return  returnMsg(400, $validate->getError());
        }
        $where['level'] = $data['level']-1;
        $result = $this->where($where)->select();

        return returnMsg(200, '添加成功',$result);

    }

    /**
     * 添加菜单
     * @param $data
     * @return mixed
     */
    public function addMenu($data){
        $validate = new Validate($this->rule['add'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return  returnMsg(400, $validate->getError());
        }

        if(1 == $data['url']){
            $data['url'] = $this->getNodeName($data['url_module']) . '/' . $this->getNodeName($data['url_controller'])  .'/' . $this->getNodeName($data['url_action']);
            $count = $this->where(['level' => $data['level'],'pid' => $data['pid'], 'url' => $data['url']])->count();
            if($count){
                return returnMsg('400','改菜单已存在');
            }
        }else{
            $data['url'] = '#';
            $count = $this->where(['level' => $data['level'],'pid' => $data['pid'], 'title' => $data['title']])->count();
            if($count){
                return returnMsg('400','改菜单已存在');
            }
        }
        $data['create_time'] = time();

        $result = $this->allowField(true)->save($data);
        if(!$result){
            return returnMsg('400','添加失败');
        }
        return returnMsg('200','添加成功');
    }

    /**
     * 获取节点名
     * @param $id
     * @return mixed
     */
    protected function getNodeName($id){
        if(isset($id)){
            $node = Node::get($id);
            return $node->toArray()['name'];
        }
        return 'index';
    }

    /**
     * 处理菜单地址
     * @param $data
     * @return array
     */
    protected function manageMenuUrl($data){
        $dataArr = explode('/',$data);
        $result = ['#','#','#'];
        if('#' != $data && is_array($dataArr)){
            $id = Node::where(['name' => $dataArr[0],'level' => 1,'pid' => 0])->field('id,title')->find();
            $result[0] = array('id' => $id->id,'name' => $id->title);
            $id = Node::where(['name' => $dataArr[1],'level' => 2,'pid' => $id['id']])->field('id,title')->find();
            $result[1] = array('id' => $id->id,'name' => $id->title);
            $id = Node::where(['name' => $dataArr[2],'level' => 3,'pid' => $id['id']])->field('id,title')->find();
            $result[2] = array('id' => $id->id,'name' => $id->title);
        }
        return $result;
    }

    /**
     * 获取编辑菜单的详细信息
     * @param $data
     * @return array|false|\PDOStatement|string|Model
     */
    public function get_edit_menu($data){
        $validate = new Validate($this->rule['del'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return  returnMsg(400, $validate->getError());
        }

        $menu = $this->where($data)->find();
        //添加父级名称
        $result1 = $this->where(['level'=>$menu['level']-1])->select();
        $menu['parent'] = $result1;
        $menu['urlm'] = 1;
        if('#' == $menu['url']){
            $menu['urlm'] = 0;
        }
        $menu['url'] = $this->manageMenuUrl($menu['url']);
        return $menu;
    }

    /**
     * 编辑菜单
     * @param $data
     * @return mixed
     */
    public function editMenu($data){
        $validate = new Validate($this->rule['update'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return  returnMsg(400, $validate->getError());
        }

        if(1 == $data['url']){
            $data['url'] = $this->getNodeName($data['url_module']) . '/' . $this->getNodeName($data['url_controller'])  .'/' . $this->getNodeName($data['url_action']);
            $count = $this->where(['level' => $data['level'],'pid' => $data['pid'], 'url' => $data['url'],'id'=>array('neq',$data['id'])])->count();
            if($count){
                return returnMsg('400','改菜单已存在');
            }
        }else{
            $data['url'] = '#';
            $count = $this->where(['level' => $data['level'],'pid' => $data['pid'], 'title' => $data['title'],'id'=>array('neq',$data['id'])])->count();
            if($count){
                return returnMsg('400','改菜单已存在');
            }
        }

        $result = $this->allowField(true)->save($data,['id' => $data['id']]);
        if(false === $result){
            return returnMsg('400','修改失败');
        }
        return returnMsg('200','修改成功');
    }

    /**
     * 设置状态
     * @param $data
     * @return mixed
     */
    public function setStatus($data)
    {
        $validate = new Validate($this->rule['set_status'],$this->msg);
        $result   = $validate->check($data);
        if(!$result){
            return  returnMsg(400, $validate->getError(),$data);
        }

        $where['id'] = $data['id'];
        if(1 == $data['status']){
            $data['status'] = 0;
        }elseif(0 == $data['status']){
            $data['status'] = 1;
        }
        $result = $this->where($where)->update($data);

        if(1 !== $result){
            return  returnMsg(400, '请求失败');
        }
        return  returnMsg(200, '设置成功');
    }

    /**
     * 删除菜单
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function delMenu($data){
        $validate = new Validate($this->rule['del'],$this->msg);
        $result = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError());
        }

        $is_exists = $this->where(['pid' => $data['id']])->count();
        if($is_exists){
            return returnMsg(400, '该菜单还有子菜单存在，不能删除');
        }

        $result = $this->where($data)->delete();
        if(false === $result){
            return returnMsg(400, '删除失败');
        }
        return returnMsg(200, '删除成功');
    }
}














