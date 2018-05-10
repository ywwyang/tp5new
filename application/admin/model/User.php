<?php
/**
 * 用户模型
 * User: v_ywwyang
 * Date: 2017/10/26
 * Time: 11:36
 */
namespace app\admin\model;

use think\Hook;
use think\Model;
use think\Db;
use think\Validate;

class User extends Model
{
    protected $table = 'user';
    protected $type = [
        'status'    =>  'integer',
        'sex'    =>  'integer',
        'phone'    =>  'integer',
        'login_num'    =>  'integer',
        'createtime'    =>  'integer',
    ];
    protected $insert = ['last_login_ip'];
    protected $update = ['last_login_ip'];

    public $rule = [
        'add' => [
            'uname'  => 'require|alphaNum|min:5|max:18',
            'password'   => 'require|min:5|max:32',
            'real_name'  => 'require|chsAlpha|min:2|max:8',
        ],
        'edit' => [
            'id'         => 'require|integer',
            'uname'  => 'require|alphaNum|min:5|max:18',
            'password'   => 'require|min:5|max:32',
            'real_name'  => 'require|chsAlpha|min:2|max:8',
        ],
        'del' => [
            'id'         => 'require|integer',
        ],
        'setStatus' => [
            'id'         => 'require|integer',
            'status'     => 'require|integer',
        ]
    ];

    public $msg = [
        'uname.require'  => '账号必须',
        'uname.min'      => '账号最少不能少于5个字符',
        'uname.max'      => '账号最多不能超过18个字符',
        'uname.alphaNum' => '账号只能是数字或英文',
        'password.require'   => '密码必须',
        'password.min'       => '密码最少不能少于5个字符',
        'password.max'       => '密码最多不能超过32个字符',
        'real_name.require'  => '真实姓名必须',
        'real_name.chsAlpha' => '真实姓名只能是中文或英文',
        'real_name.min'      => '真实姓名最少不能少于2个字符',
        'real_name.max'      => '真实姓名多不能超过8个字符',
        'id.require'         => '用户参数缺少，请联系管理员！,',
        'id.integer'         => '用户参数错误，请联系管理员！',
        'status.require'     => '状态码必须',
        'status.integer'     => '状态码只能为整数',
    ];

    /**
     * 新增活跟新时对 LastLoginIp 字段进行自动记录
     * @return mixed
     */
    protected function setLastLoginIpAttr()
    {
        return request()->ip();
    }

    /**
     * 列表展示
     * @return array
     */
    public function getParentInfo(){
        // 查询角色信息
        $role   = $this->alias('a')
            ->join('role_user b','a.id = b.user_id')
            ->join('role c','c.id = b.role_id')
            ->field('c.name,b.user_id')
            ->select();
        // 查询总条数
        $count  = $this->count();
        $user = $this->field('password',true)->paginate(config('paginate.list_rows'));
        $result = [
            'count'  => $count,
            'user' => $user,
            'role'   => $role
        ];
        if (false === $user) {
            return returnMsg(400, '系统错误，请联系管理员！');
        }
        Hook::listen('app_user');
        return $result;
    }

    /**
     * 添加后台用户
     * @param $data
     * @return bool|false|int
     */
    public function addUser($data){
        $validate = new Validate($this->rule['add'],$this->msg);
        $result   = $validate->check($data);
        if(!$result){
            return  returnMsg(400, $validate->getError());
        }

        $is_exists = $this->where(['uname' => $data['uname']])->count();

        if($is_exists){
            return  returnMsg(400, '该用户已存在');
        }
        $data['password'] = md5($data['password']);
        $result = $this->save($data);

        if(false === $result){
            return returnMsg(400, '添加失败，请联系管理员！');
        }
        return returnMsg(200, '添加成功，是否返回列表页？');
    }

    /**
     * 获取修改用户的所有信息
     * @param $data
     * @return array|bool|false|\PDOStatement|string|Model
     */
    public function getUser($data){
        $validate = new Validate($this->rule['del'],$this->msg);
        $result   = $validate->check($data);

        if(!$result){
            return returnMsg(400, $validate->getError());
        }

        $result = $this->where(['id' => $data['id']])->find();
        return $result;
    }

    /**
     * 修改用户信息
     * @param $data
     * @return $this|bool
     */
    public function editUser($data){
        $validate = new Validate($this->rule['edit'],$this->msg);
        $result   = $validate->check($data);
        if(!$result){
            return returnMsg(400, $validate->getError());
        }


        // 检测是否重名
        $count = $this->where(['uname'=>$data['uname'],'id'=>array('neq',$data['id'])])->count();
        if ($count) {
            return  returnMsg(400, '该账号已被注册');
        }
        //用md5值判断密码是否已修改
        $pwdCount = $this->where(['id'=>$data['id'],'password' => $data['password']])->count();
        if(!$pwdCount){
            $data['password'] = md5($data['password']);
        }

        $result = $this->allowField(true)->save($data,['id' => $data['id']]);
        if(false === $result){
            return returnMsg(400, '修改失败,请联系管理员！');
        }

        return returnMsg(200, '修改成功，是否返回列表页？');
    }


    /**
     * 设置状态
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function setStatus($data)
    {
        $validate = new Validate($this->rule['setStatus'],$this->msg);
        $result   = $validate->check($data);
        if(!$result){
            return  returnMsg(400, $validate->getError());
        }

        if(1 == $data['status']){
            $hint = '已禁止';
            $data['status'] = 0;
        }elseif(0 == $data['status']){
            $hint = '已启用';
            $data['status'] = 1;
        }
        $result = $this->allowField(true)->save($data,['id'=>$data['id']]);

        if(false === $result){
            return  returnMsg(400, '请求失败,请联系管理员！');
        }
        return  returnMsg(200, '设置成功，'.$hint) ;
    }

    /**
     * 删除用户
     * @param $data
     * @return mixed
     * @throws \think\Exception
     */
    public function delUser($data){
        $validate = new Validate($this->rule['del'],$this->msg);
        $result   = $validate->check($data);
        if(!$result){
            return returnMsg(400, $validate->getError());
        }

        $result = $this->where($data)->delete();
        if(!$result){
            return returnMsg(400, '删除失败，请联系管理员！');
        }
        return  returnMsg(200, '删除成功');
    }

    /**
     * 获取用户信息
     * @param $userId
     * @return array|false|\PDOStatement|string|Model
     */
    public function getUserInfo($userId){
        if (isset($userId)) {
            $userInfo = $this->where(['id' => $userId])->find();
            return $userInfo;
        }
    }

    /**
     * 获取角色信息
     * @param $id
     * @return array
     */
    public function getRoleInfo($id){
        $info = Role::field('id,name')->select();
        $roleInfo = Db::name('role_user')->where(['user_id'=>$id])->column('role_id');
        return [$info,$roleInfo];
    }

    protected function role(){
        return $this->belongsToMany('Role','\app\admin\model\RoleUser');
    }

    public function test()  {
        $user = $this->with('role')->select([1,2]);get_re($user);die;
        $roles = $user->roles;
        foreach($roles as $role){
            // 获取中间表数据
            get_re($role->id);
        }

    }

    /**
     * 分配角色
     * @param $data
     * @return int|string
     * @throws \think\Exception
     */
    public function AddUserRole($data){
        $info=[];
        if (is_array($data['roleId'])) {
            foreach ($data['roleId'] as $value) {
                $info[] = [
                    'user_id' => $data['userId'],
                    'role_id' => $value,
                ];
            }
        }
        // 先删除
        Db::name('role_user')->where(['user_id'=>$data['userId']])->delete();
        // 再插入
        $result = Db::name('role_user')->insertAll($info);
        if (false === $result) {
            return returnMsg(400,'操作有误，请刷新重试');
        }
        return returnMsg(200,'角色分配成功，是否返回列表页');
    }

    /**
     * 保存新密码
     * @return [type] [description]
     */
    public function savePwd($info){
        $validate = new Validate($this->rule['add'],$this->msg);
        $validate->scene('savePwd', ['password']);
        $result   = $validate->scene('savePwd')->check($info);
        if (!$result) {
            return returnMsg('400',$validate->getError());
        }
        $result = $this->update(['password' => $info['password'],'id' => $info['id']]);
        if ($result) {
            return returnMsg(200, '保存成功');
        } else {
            return returnMsg(400, '保存失败，请联系管理员！');
        }
    }
}