<?php
namespace Home\Controller;
use Think\Controller;
class PublicController extends Controller {
    public function index(){
            print json_encode('err');//$this->display('vision:index');

    }

    //验证码
    public function verify() {
        ob_clean();
        Image::buildImageVerify();
    }

    // 用户登出
    public function loginout() {
        if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
            $vision=array();
            $this->returnApiSuccess('',$vision);
        }else {
            $this->returnApiError('用户已经退出！');
        }
    }

    // 登录检测
    public function checkLogin($account,$password) {
        if(empty($account)) {
            $this->returnApiError('帐号为空');
        }elseif (empty($password)) {
            $this->returnApiError('密码为空');
        }
        $data = array();
        $data['ip']    = get_client_ip();
        $data['date']  = date("Y-m-d H:i:s");
        $data['username'] = $account;
        $data['module'] = MODULE_NAME;
        $data['action'] = ACTION_NAME;
        $data['querystring'] = U( MODULE_NAME . '/' . ACTION_NAME );

        $studentInfo = D("student")->where('phone = "%s" and password = "%s"',$account,$password)->order("sid desc")->select();

        if(!$studentInfo) {
            $this->returnApiError('用户名或者密码错误！');
        }
        else {
            if(!$studentInfo[0]['status']) {
                $this->returnApiError('该用户已经被禁用！');
            }
            $_SESSION['student_info'] = $studentInfo;
            //保存登录信息
            D('Student')->where( array( 'sid' => $studentInfo[0]['sid'] ) )->save( array( 'last_login_time' => time(), 'last_login_ip' => $data['ip'] ) );

            //保存日志
            $data['status'] = 1;
            //$data['userid'] = $studentInfo['sid'];
            D("Log")->add( $data );
            $this->returnApiSuccess('',$studentInfo);
        }
    }

    /**
     * @param null $msg  返回正确的提示信息
     * @param flag success CURD 操作成功
     * @param array $data 具体返回信息
     * Function descript: 返回带参数，标志信息，提示信息的json 数组
     *
     */
    function returnApiSuccess($msg = null,$data = array()){
        $result = array(
            'flag' => 'Success',
            'msg' => $msg,
            'data' =>$data
        );
        print json_encode($result);
        exit();
    }

    /**
     * @param null $msg  返回具体错误的提示信息
     * @param flag success CURD 操作失败
     * Function descript:返回标志信息 ‘Error'，和提示信息的json 数组
     */
    function returnApiError($msg = null){
        $result = array(
            'flag' => 'Error',
            'msg' => $msg,
        );
        print json_encode($result);
        exit();
    }

}