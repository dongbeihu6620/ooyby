<?php
namespace Api\Controller;
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
    public function checkLogin($school,$grade,$classes,$number) {
        if(empty($grade)) {
            $this->returnApiError('年级为空');
        }
        if(empty($classes)) {
            $this->returnApiError('班级为空');
        }
        if(empty($number)) {
            $this->returnApiError('学号为空');
        }
        if(empty($school)) {
            $this->returnApiError('学校为空');
        }
        $data = array();
        $data['ip']    = get_client_ip();
        $data['date']  = date("Y-m-d H:i:s");
        $data['username'] = $number;
        $data['module'] = MODULE_NAME;
        $data['action'] = ACTION_NAME;
        $data['querystring'] = U( MODULE_NAME . '/' . ACTION_NAME );

        $school = D("school")->field('id')->where('name = "%s"',$school)->select();
        if (empty($school)){
            $this->returnApiError('该学校名称不存在');
        }

        $classes = D("classes")->where('classes = "%s" and grade = "%s" and sid = %d',$classes,$grade,$school[0]['id'])->order("sid desc")->select();
        if (empty($classes)){
            $this->returnApiError('年级或班级不存在');
        }

        $studentInfo = D("student")->where('classes = %d and school = %d and number = "%s"',$classes[0]['id'],$school[0]['id'],$number)->order("sid desc")->select();

        if(!$studentInfo) {
            $this->returnApiError('学号错误！');
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

    // 引导图
    public function guidemap() {
        $GuidemapInfo = D("Guide_map")->where("status = 1")->order("orderlist desc")->select();
        if($GuidemapInfo) {
            $this->returnApiSuccess('',$GuidemapInfo);
        }
        else {
            $this->returnApiError('数据为空');
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