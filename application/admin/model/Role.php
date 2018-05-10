<?php
/**
 * 角色模型
 * User: v_ywwyang
 * Date: 2018/5/2
 * Time: 11:45
 */

namespace app\admin\model;


use think\Model;
use think\Validate;

class Role extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'role';

    // 验证规则
    protected $rules =   [
        'add' => [
            'name'   => 'require|max:15',
            'pid'    => 'number',
            'status' => 'number'
        ],
        'edit' => [
            'id' 	 => 'require|number',
            'name'   => 'require|max:15',
            'pid'    => 'number',
            'status' => 'number'
        ],
        'delete' => [
            'id' 	 => 'require|number'
        ]
    ];
    protected $message  =   [
        'id.require' 	=> '缺失必要参数，请返回重新操作',
        'id.number' 	=> '缺失必要参数，请返回重新操作',
        'name.require' 	=> '名称必须',
        'name.max'     	=> '名称最多不能超过15个字符',
        'pid.number'   	=> '父级参数错误，请联系管理员',
        'status.number' => '年龄必须是数字'
    ];
    protected $scene = [
        'add'     =>  ['name','pid','status'],
        'edit'    =>  ['id','name','pid','status'],
        'delete'  =>  ['id']
    ];

    /**
     * 获取添加角色所需信息
     * @return mixed
     */
    public function getAddInfo(){
        $result = $this->field('id,name')->select();
        if (false === $result) {
            return returnMsg(400, '未找到角色信息，请重新操作');
        }
        return returnMsg(200,'',$result);
    }

    /**
     * 获取角色信息
     * @param string $roleId
     * @return array
     */
    public function getParentInfo($roleId=''){
        if ($roleId) {
            $result = $this->where(['id'=> $roleId])->find();
            // 父级名称
            $parentName = $this->field('id,name')->where(['id'=>array('NEQ',$roleId)])->select();
            $data = [
                'result'     => $result,
                'parentName' => $parentName
            ];
            return $data;
        }else {
            // 查询总条数
            $count = $this->count();
            $result = $this->paginate(config('paginate.list_rows'))->each(function($item, $key){
                    $parentName = $this->field('name')->where(['id'=>array('EQ',$item->pid)])->find();
                    $item->pid_name = $parentName['name'];
                }
            );
            $result = [
                'count'      => $count,
                'result'     => $result
            ];
            if (false === $result) {
                return returnMsg(400, '请重新操作');
            }
            return $result;
        }
    }

    /**
     * 添加角色
     * @param $data
     * @return mixed
     */
    public function addRole($data){
        // 数据验证
        $validate = new Validate($this->rules['add'],$this->message);
        $result = $validate->scene('add')->check($data);
        if (!$result) {
            return returnMsg(400, $validate->getError());
        }

        // 有这个角色了就不添加
        $name = $this->where(['name'=> $data['name']])->count();
        if ($name) {
            return returnMsg(400, '该角色已存在');
        }
        // 没有就添加数据
        $result = $this->allowField(true)->save($data);
        if(false === $result){
            return returnMsg(400, '角色添加失败!');
        }
        return returnMsg(200, '添加成功，是否返回列表？');
    }

    /**
     * 角色编辑
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function editRole($data){
        // 数据验证
        $validate = new Validate($this->rules['edit'],$this->message);
        $result = $validate->scene('edit')->check($data);
        if (!$result) {
            return returnMsg(400, $validate->getError());
        }

        // 有这个角色了就不添加
        $name = $this->where(['name'=> $data['name'],'id'=>array('neq',$data['id'])])->count();
        if ($name) {
            return returnMsg(400, '该角色已存在');
        }
        $result = $this->allowField(true)->save($data,['id' => $data['id']]);
        if(false === $result){
            return returnMsg(400, '角色修改失败!');
        }
        return returnMsg(200, '修改成功，是否返回列表页？');
    }

    /**
     * 删除
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function delRole($data){
        // 数据验证
        $validate = new Validate($this->rules['delete'],$this->message);
        $result = $validate->scene('delete')->check($data);
        if (!$result) {
            return returnMsg(400, $validate->getError());
        }

        $result = $this->where(['id'=> $data['id']])->delete();
        if(false === $result){
            return returnMsg(400, '角色删除失败!');
        }
        return returnMsg(200, '角色删除成功!');
    }

    /**
     * 状态修改
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function setStatus($data){
        //状态更替
        if (0 === intval($data['status'])) {
            $data['status'] = 1;
            $hint = '已启用';
        }elseif (1 === intval($data['status'])){
            $data['status'] = 0;
            $hint = '已禁止';
        }
        $result = $this->where(['id'=> $data['id']])->update(['status' => $data['status']]);
        if (false === $result) {
            return returnMsg(400, '设置成功，'.$hint);
        }
        return returnMsg(200, '设置成功，'.$hint);
    }
}