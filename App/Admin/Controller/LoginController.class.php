<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2015 http://www.hhailuo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: XiaoYao <476552238li@gmail.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Controller;
use Org\Util\Image as Image;
use Org\Util\Rbac as RBAC;

class LoginController extends Controller {
    public function index() {
        header("Location:".__MODULE__."/Login/login");
    }

    //验证码
    public function verify() {
        $type = isset($_GET['type']) ? $_GET['type'] : 'gif';
        Image::buildImageVerify( 4, 1, $type);
    }

    public function login() {
        if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
        }else{
            header("Location:".__MODULE__.'/Login/Index');
        }
    }

    // 用户登出
    public function loginout() {
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
            $this->success('登出成功！',__CONTROLLER__.'/Login/login/');
        }else {
            $this->error('已经登出！');
        }
    }

    // 登录检测
    public function checkLogin() {
        if(empty($_POST['account'])) {
            $this->assign('jumpUrl', __MODULE__ . C('USER_AUTH_GATEWAY'));
            $this->error('帐号错误！');
        }elseif (empty($_POST['password'])) {
            $this->assign('jumpUrl', __MODULE__ . C('USER_AUTH_GATEWAY'));
            $this->error('密码必须！');
        }elseif (empty($_POST['verify'])) {
            $this->assign('jumpUrl', __MODULE__ . C('USER_AUTH_GATEWAY'));
            $this->error('验证码必须！');
        }
        // 生成认证条件
        $map = array();
        // 支持使用绑定帐号登录
        $map['phone'] = $_POST['account'];
        $map["status"] = array('gt',0);

        $data = array();
        $data['ip']    = get_client_ip();
        $data['date']  = date("Y-m-d H:i:s");
        $data['username'] = $_POST['account'];
        $data['module'] = MODULE_NAME;
        $data['action'] = ACTION_NAME;
        $data['querystring'] = U( MODULE_NAME . '/' . ACTION_NAME );

        if($_SESSION['verify'] != md5($_POST['verify'])) {
            $this->assign('jumpUrl', __MODULE__ . C('USER_AUTH_GATEWAY'));
            $this->error('验证码错误！');
        }
        $authInfo = RBAC::authenticate($map,"Agent");
        $authInfo['role_id'] = 3;
        //dump ($authInfo);
        //exit();
        //使用用户名、密码和状态的方式进行认证
        if(false === $authInfo) {
            $data['status'] = 0;
            D("Log")->add( $data );
            $this->assign('jumpUrl', __MODULE__ . C('USER_AUTH_GATEWAY'));
            $this->error('帐号不存在或已禁用！');
        } else {

            if($authInfo['password'] != md5($_POST['password'])) {
                $data['status'] = 0;
                D("Log")->add( $data );
                $this->assign('jumpUrl', __MODULE__ . C('USER_AUTH_GATEWAY'));
                $this->error('密码错误！');
            }
            $_SESSION['user_info'] = $authInfo;
            $_SESSION[C('USER_AUTH_KEY')] = 46;
            $_SESSION['lastLoginTime'] = $authInfo['last_login_time'];

            // 站点ID设置
            $_SESSION['siteid'] = SITEID;
            if($authInfo['role_id']==1) {
                $_SESSION['administrator']      =   true;
            }
            //保存登录信息
            D('agent')->where( array( 'aid' => $authInfo['aid'] ) )->save( array( 'last_login_time' => time(), 'last_login_ip' => $data['aid'] ) );

            //保存日志
            $data['status'] = 1;
            $data['userid'] = $authInfo['aid'];
            //dump($data);
            //exit();
            D("Log")->add( $data );
            //dump(D("Log")->add( $data ));
            //exit();
            // 存储访问权限
            RBAC::saveAccessList(46);
            $this->success('登录成功！',__MODULE__.'/Index');
        }
    }
}