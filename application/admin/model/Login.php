<?php
// +----------------------------------------------------------------------
// | description 登录
// +----------------------------------------------------------------------
namespace app\admin\model;

use  app\admin\model;
use think\Validate;


class Login
{
    protected $rule  = [
		'uname' => 'require|min:5|max:18',
        'password'  => 'require|min:5|max:32'
    ];

    protected $message  = [
        'uname.require'  => '用户名必须',
    	'uname.alphaNum' => '账号密码有误',
        'uname.min'      => '账号密码有误',
        'uname.max'      => '账号密码有误',
        'password.require'   => '密码必须',
        'password.min'     	 => '账号密码有误',
        'password.max'     	 => '账号密码有误',
    ];

    public function validateLogin($data){
    	// 数据验证
		$validate = new Validate($this->rule,$this->message);
        $result = $validate->check($data);
        if (!$result) {
            return returnMsg(400, $validate->getError());
        }

        // 查询这个用户信息
    	$userInfoData = User::get([ 'uname'=> $data['uname'] ]);
    	if (!$userInfoData) {
    		return returnMsg(400, '用户名有误');
    	} else if ($userInfoData['password'] != md5($data['password'])) {
    		return returnMsg(400, '密码错啦');
    	} else if ($userInfoData['status'] == 0 ) {
            return returnMsg(400, '该用户已被锁定，暂时不可登录');
        }else {
            //更新登录信息
            $newData = [
                'last_login_time' => time(),
                'login_num'       => $userInfoData['login_num'] + 1
            ];
            User::update($newData,[ 'uname'=> $data['uname'] ]);
    		return returnMsg(200,'登录成功',$userInfoData);
    	}
    }

}
