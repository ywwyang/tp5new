<?php
/**
 * 节点模型
 * User: v_ywwyang
 * Date: 2018/4/28
 * Time: 15:08
 */

namespace app\admin\model;

use think\Model;
use think\Db;
use think\Validate;

class Node extends Model
{
    protected $table = 'node';

    public $rule = [
        'add' => [
            'name'  => 'require',
            'title'   => 'require',
            'status' => 'require',
            'sort'   => 'number',
            'pid' => 'require|number',
            'level' => 'require|number',
        ],
        'edit' => [
            'id' => 'require|integer',
            'name'  => 'require',
            'title'   => 'require',
            'status' => 'require',
            'sort'   => 'number',
            'pid' => 'require|number',
            'level' => 'require|number',
        ],
        'del' => [
            'id' => 'require|integer',
        ],
        'level'=>[
            'level' => 'require|number',
        ],
        'set_status'=>[
            'id' => 'require|integer',
            'status' => 'require|integer',
        ]
    ];

    public $msg = [
        'name.require' => '名称必须',
        'title.require' => '标题必须',
        'status.require' => '状态必须',
        'sort.number' => '排序需为数字',
        'pid.require' => '父级必须',
        'pid.number' => '父ID需为数字',
        'level.require' => '节点规定必须',
        'level.number' => '申明必须非法',
        'id.require' => '用户ID失效',
        'id.integer' => '用户ID只能为整数',
        'status.integer' => '状态码只能为整数',
    ];

    /**
     * 获取节点列表
     * @return mixed
     */
    public function getTreeList(){
        $result = $this->field('id,pid as pId,status,title as name')->where([])->select();
        foreach($result as $key=>$val){//配置状态图标
            if(1 == $val['status']){
                $result[$key]['icon'] = config('tree.yes');
            }else{
                $result[$key]['icon'] = config('tree.no');
            }
        }
        return $result;
    }

    /**
     * 异步获取父节点
     * @param $data
     */
    public function getNode($data){
        $validate = new Validate($this->rule['level'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError()) ;
        }
        $data['status'] = 1;
        $data['level'] = $data['level']-1;
        $node_list= $this->where($data)->select();
        if(!$node_list){
            return returnMsg(400, '未创建父节点');
        }
        return returnMsg(200, '成功',$node_list);
    }

    /**
     * 添加节点
     * @param $data
     * @return bool|false|int
     */
    public function addNode($data){
        $validate = new Validate($this->rule['add'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError());
        }

        $is_exists = $this->where(['name' => $data['name'],'level' => $data['level'],'pid'=>$data['pid']])->count();
        if($is_exists){
            return returnMsg(400, '该节点已存在');
        }
        $result = $this->save($data);
        if(false === $result){
            return returnMsg(400, '添加失败');
        }
        return returnMsg(200, '添加成功');
    }

    /**
     * 获取节点的详细信息
     * @param $data
     * @return array|bool|false|\PDOStatement|string|Model
     */
    public function getNode_details($data){
        $validate = new Validate($this->rule['del'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError());
        }
        $result = $this->where($data)->find();
        //添加父级名称
        $result1 = $this->field('title')->where(['id'=>$result['pid']])->find();
        $result['pid_name'] = $result1['title'];

        return $result;
    }

    /**
     * 更新节点信息
     * @param $data
     * @return $this|bool
     */
    public function editNode($data){
        $validate = new Validate($this->rule['edit'],$this->msg);
        $result = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError());
        }

        $where = array('name' => $data['name'],'level' => $data['level']);
        $where['pid'] = array('eq',$data['pid']);
        $where['id'] = array('neq',$data['id']);

        $is_exists = $this->where($where)->count();
        if($is_exists){
            return returnMsg(400, '该节点已存在111');
        }

        $result = $this->where(['id' => $data['id']])->update($data);

        if(false === $result){
            return returnMsg(400, '修改失败');
        }
        return returnMsg(200, '修改成功');
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
            return  returnMsg(400, $validate->getError());
        }
        $where['id'] = $data['id'];
        if(1 === intval($data['status'])){
            $data['status'] = 0;
        }elseif(0 === intval($data['status'])){
            $data['status'] = 1;
        }
        $result = $this->where($where)->update($data);
        if(1 !== $result){
            return  returnMsg(400, '请求失败');
        }
        return returnMsg(200, '设置成功',$result);
    }

    /**
     * 删除节点
     * @param $data
     * @return int
     */
    public function delNode($data){
        $validate = new Validate($this->rule['del'],$this->msg);
        $result = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError());
        }
        $is_exists = $this->where(['pid' => $data['id']])->count();
        if($is_exists){
            return returnMsg(400, '该节点还有子节点存在，不能删除');
        }
        $result = $this->where($data)->delete();
        if(false === $result){
            return returnMsg(400, '删除失败');
        }
        return returnMsg(200, '删除成功');
    }
}