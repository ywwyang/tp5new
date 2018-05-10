<?php
/**
 * 权限管理模型
 * User: v_ywwyang
 * Date: 2018/5/2
 * Time: 11:45
 */
namespace app\admin\model;

use think\Db;
use think\Model;
use think\Validate;

class Access extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'access';

    // 验证规则
    protected $rules = [
        'add' => [
            'role_id'   => 'require|number',
            'node_id'	=> 'require|number',
            'level' 	=> 'number'
        ],
        'index' => [
            'id'  => 'require|number',
        ]
    ];
    protected $message  = [
        'role_id.require' 	=> '角色ID必须',
        'role_id.number' 	=> '角色ID必须是数字',
        'node_id.require' 	=> '节点ID必须',
        'node_id.number' 	=> '节点ID必须是数字',
        'id.require' 	=> '角色ID必须',
        'id.number' 	=> '角色ID必须是数字',
    ];
    protected $scene = [
        'add'     =>  ['role_id','node_id'],
        'index'   =>  ['id'],
    ];

    /**
     * 添加权限
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function addAccess($data){
        // 先删除已有的权限
        $resultData = $this->where(['role_id'=> $data['role_id']])->delete();
        if (isset($data['node_id'])) {
            // 启动事务
            Db::startTrans();
            $arrayData = [];
            try{
                foreach ($data['node_id'] as $value) {
                    $data['node_id'] = $value;
                    // 数据验证
                    $validate = new Validate($this->rules['add']);
                    $result = $validate->scene('add')->check($data);
                    if (!$result) {
                        return returnMsg(400, $validate->getError());
                    }
                    $arrayData[] = [
                        'role_id' => $data['role_id'],
                        'node_id' => $data['node_id'],
                    ];
                }
                // 添加权限
                $this->insertAll($arrayData);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return returnMsg(400, '权限添加失败!');
            }
        }
        return returnMsg(200, '权限添加成功!');
    }

    /**
     * 获取权限信息
     * @param $data
     * @return array|bool
     */
    public function getAccessInfo($data){
        // 数据验证
        $validate = new Validate($this->rules['index'],$this->message);
        $result = $validate->scene('index')->check($data);
        if (!$result) {
            return returnMsg(400, $validate->getError());
        }

        $roleId = $data['id'];
        // 获取该用户已有的节点权限信息
        $haveInfo = $this->where(['role_id'=>$roleId])->column('node_id');
        // 获取节点表里状态不等于0的节点
        $nodeAll =Node::where('status','NEQ', 0)->field('id,pid,title as name')->select();
        // 查询该角色的用户信息
        $role = Role::where(['id'=> $roleId])->field('id,name')->find();
        $result = [
            'haveInfo' => $haveInfo,
            'nodeAll'   => json_decode(json_encode($nodeAll),true),
            'role' => $role
        ];
        if (false === $haveInfo && false === $nodeAll && false === $role) {
            return returnMsg(400, '尚未获取到权限信息!');
        }
        return $result;
    }
}

