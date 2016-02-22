<?php
/**
 * Created by PhpStorm.
 * User: xueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */

namespace Admin\Controller;
use Admin\Controller\CommonController;

/**
 * 视力管理
 */
class ApiController extends CommonController {
    public function index() {
        $count    = D("Vision")->count();// 查询满足要求的总记录数
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $visions = D("Vision")->visionslist()->limit($page->firstRow.','.$page->listRows)->select();
        //$this->assign('page',$show);
        //$this->assign('visions', $visions);
        print json_encode($visions);//$this->display('vision:index');
    }

    public function visionslist() {
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid')
            ->field('v.vid as vid, s.sid as sid,v.left_eye as left_eye, v.right_eye as right_eye,v.add_time as add_time,s.nickname as nickname,s.realname as realname,s.gender as gender,s.age as age,s.school as school,s.grade as grade,s.classes as classes,v.status as status')
            ->order('v.vid desc');
        return $visions;
    }

    public function addvision() {

            print json_encode('err');

    }

    public function edit() {
        $vid = $_GET['vid'];
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if (D("Vision")->where(array('vid' => $_POST['vid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Vision/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Vision/index');
            }
        } else {
            if (empty($vid)) {
                $this->error('异常操作！', __MODULE__ . '/Vision/index');
            }

            $vision = D("Vision")->getvision($vid);

            $this->assign('vid', $vid);
            $this->assign("vision", $vision);
            $this->assign("student",D("Student")->getstudent($vision['sid']));
            //$this->assign("members", D("Member")->memberList());
            $this->display();
        }
    }

    public function del() {
        $vid = intval($_GET['vid']);
        if (empty($vid)) {
            $this->error('异常操作！', __MODULE__ . '/Vision/index');
        }
        $result = D('Vision')->delvision($vid);
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Vision/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Vision/index');
        }
    }


}
