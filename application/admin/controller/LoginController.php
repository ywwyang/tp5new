<?php
namespace app\admin\controller;


use app\admin\model\Login;
use think\Controller;
use think\Request;
use think\Session;

class LoginController extends Controller
{
    protected $login;

    public function __construct()
    {
        parent::__construct();
        $this->login = new Login();
    }

    /**
     * 登入视图渲染
     * @return mixed
     */
    public function index(){
        if(checkLogin()){
            $this->redirect(url("Index/index"));
            exit();
        }
        return $this->fetch('login');
    }

    /**
     * 登入验证
     * @return mixed
     */
    public function doLogin(){
        // 判断是否为isAjax请求
        $data = $this->request->param();
        if ($this->request->isAjax()) {
            // 校验验证码
            /*$code = $data['captcha'];
            $this->validate($code,[
                'captcha|验证码'=>'require|captcha'
            ]);
            $result = $this->check_verify($code);
            if ($result === false) {
                return returnMsg(400, '验证码错误');
            }*/
            $data = [
                'uname' => input('param.uname'),
                'password'  => input('param.password'),
            ];
            // 登录验证
            $userInfo =  $this->login->validateLogin($data);

            if ($userInfo['code'] === 200) {
                // 存储用户ID到session
                Session::set(Config('rbac.user_auth_key'),$userInfo['data']['id']);
            }
            return $userInfo;
        } else {
            return returnMsg(400, '请求错误');
        }
    }

    /**
     * 退出
     */
    public function loginout() {
        if (checkLogin()) {
            session(Config('rbac.user_auth_key'), null);
        }
        $this->redirect(url('Login/index'));
    }

    /**
     * 验证码校验
     * @param $code
     * @return bool
     */
    protected function check_verify($code){
        $captcha = new \think\captcha\Captcha();
        return $captcha->check($code);
    }



}
