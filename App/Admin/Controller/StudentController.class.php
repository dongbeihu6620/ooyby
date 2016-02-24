<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
/**
 * 学生管理
 */
class StudentController extends CommonController {

    public function index() {
        $aid=$_SESSION['user_info']['arrchildid'];
        $map['aid']  = array('in',$aid);
        //echo $aid;
        if ($_SESSION['user_info']['level'] != 0) {
            $count = D("Student")->where($map)->count();// 查询满足要求的总记录数
        }
        else{
            $count = D("Student")->count();
        }
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        if ($_SESSION['user_info']['level'] != 0){//如果是代理商
            $students = D("Student")->where($map)->studentsList()->limit($page->firstRow.','.$page->listRows)->select();
        }
        else{
            $students = D("Student")->studentsList()->limit($page->firstRow.','.$page->listRows)->select();
        }
        $this->assign('page',$show);
        $this->assign('students', $students);
        $this->display('student:index');
    }

    public function allstudents() {

        $count    = D("Student")->count();// 查询满足要求的总记录数
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $students = D("Student")->order("sid desc")->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('students', $students);
        $this->display('student:index');
    }

    //显示某学生视力数据
    public function studentvision()
    {
        $sid = $_GET['sid'];
        $name = $_GET['name'];
        $count = D("Vision")->where('sid = %d',$sid)->count();// 查询满足要求的总记录数
        $pagecount = 20;
        $page = new \Think\Page($count, $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first', '首页');
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $page->setConfig('last', '尾页');
        $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $visions = D("student")->studentvision($sid)->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('name',$name);
        $this->assign('visions', $visions);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['add_date']=strtotime($_POST['info']['add_date']);
            $_POST['info']['password']=md5($_POST['info']['password']);
            $_POST['info']['aid']=$_SESSION['user_info']['id'];
            if (D("Student")->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("students", D("student")->studentsList());
            $this->display();
        }
    }

    public function edit() {
        $sid = $_GET['sid'];
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['add_date']=strtotime($_POST['info']['add_date']);
            if (D("Student")->where(array('sid' => $_POST['sid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Student/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Student/index');
            }
        } else {
            if (empty($sid)) {
                $this->error('异常操作！', __MODULE__ . '/Member/index');
            }
            $this->assign('sid', $sid);
            $this->assign("student", D("Student")->getstudent($sid));
            //$this->assign("members", D("Member")->memberList());
            $this->display();
        }
    }

    public function del() {
        $sid = intval($_GET['sid']);
        if (empty($sid)) {
            $this->error('异常操作！', __MODULE__ . '/Student/index');
        }
        $result = D('Student')->delstudent($sid);
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Student/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Student/index');
        }
    }

    public function listorder() {
        if (isset($_POST['sort']) && is_array($_POST['sort'])) {
            $sort = $_POST['sort'];
            foreach ($sort as $k => $v) {
                $this->db->where(array('id'=>$k))->save(array('sort'=>$v));
            }
        }
        $this->success('排序成功');
    }

    function impstudent(){
        if(isset($_FILES["import"]) && ($_FILES["import"]["error"] == 0)){
            $result =D("Student")->importExecl($_FILES["import"]["tmp_name"]);
            if($result["error"] == 1){
                $execl_data = $result["data"][0]["Content"];
                $data = array();
                $i = 0;
                foreach($execl_data as $k=>$V){
                   if ($i > 0) {
                       $data['realname']    = $V['0'];
                       $data['age']         = $V['1'];
                       if ($V['2'] == "男"){
                       $data['gender']  = 1;
                       }
                       else if($V['2'] == "女"){
                            $data['gender']  = 2;
                       }
                       $data['phone']       = $V['3'];
                       $data['school']      = $V['4'];
                       $data['grade']       = $V['5'];
                       $data['classes']     = $V['6'];
                       $data['left_eye']    = $V['7'];
                       $data['right_eye']   = $V['8'];
                       $data['add_date']    = strtotime($V['9']);
                       $data['aid']    = $V['10'];
                       $data['password']    = md5('123456');
                       $data['status']      = '1';

                       if (D("Student")->add($data) > 0) {
                           $this->success('操作成功！', "index");
                       }
                       else {
                           $this->error('操作失败！');
                       }
                   } ;
                    $i++;
                }

            }
        }
        else{
            $this->display();
        }
    }

}
