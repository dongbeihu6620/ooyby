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
class MessageController extends CommonController {
    private $db, $member_db, $user_db;
    function __construct() {
        parent::__construct();
        $this->db = D("Message");
        $this->member_db = D('Member_message');
        $this->user_db = D('Member');
        ;
    }

    public function index() {
        $count = $this->db->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $messages = $this->db->limit($page->firstRow.','.$page->listRows)->order('mid desc')->select();

        $this->assign('page',$show);
        $this->assign('messages', $messages);
        $this->display();
    }

    public function messagelist($mid,$title) {
        if(empty($mid))
        {
            $this->error('操作失败！');
        }
        $count = $this->member_db->where('mid = %d',$mid)->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $messages = $this->member_db->limit($page->firstRow.','.$page->listRows)->where('mid = %d',$mid)->order('id desc')->select();

        $this->assign('page',$show);
        $this->assign('title',$title);
        $this->assign('messages', $messages);
        $this->display();
    }

    public function messagedel() {
        $id = intval($_GET['id']);
        $mid = intval($_GET['id']);
        $title = intval($_GET['title']);
        if (empty($id)) {
            $this->error('异常操作！', __MODULE__ . '/message/messagelist/mid/$mid/title/$title');
        }
        $result = $this->member_db->where("id = %d", $id)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/message/messagelist/mid/$mid/title/$title');
        } else {
            $this->error('操作失败！', __MODULE__ . '/message/messagelist/mid/$mid/title/$title');
        }
    }

    public function add() {
        if (IS_POST) {
            $this->checkToken();
            //发送者信息
            $authorid=$_SESSION['user_info']['id'];
            $author=$_SESSION['user_info']['account'];
            if(empty($authorid) || empty($author))
            {
                $this->error('操作失败！');
            }
            $uid = $_POST['uid'];
            if(empty($uid)){
                $this->error('操作失败！');
            }
            $arr = explode(",",$uid);
            $_POST['info']['members']=count($arr);
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            $_POST['info']['authorid']=$authorid;
            $_POST['info']['author']=$author;
            $_POST['info']['status']=1;

            if ($this->db->add($_POST['info']) > 0) {
                //添加接收用户消息
                $_POST['member']['status']=0;
                $_POST['member']['dateline']=$_POST['info']['dateline'];
                $mid=$this->db->field('mid')->limit(1)->order('mid desc')->select();
                $_POST['member']['mid']=$mid[0]['mid'];
                foreach($arr as $message){
                    $_POST['member']['uid']=$message;
                    $this->member_db->add($_POST['member']);
                 }
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("messages", $this->db->select());
            $this->display();
        }
    }

    public function addall() {
        if (IS_POST) {
            $this->checkToken();
            //发送者信息
            $authorid=$_SESSION['user_info']['id'];
            $author=$_SESSION['user_info']['account'];
            if(empty($authorid) || empty($author))
            {
                $this->error('操作失败！');
            }
            //获取所有家长的uid
            $arr =$this->user_db->field(uid)->select();
            $_POST['info']['members']=count($arr);
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            $_POST['info']['authorid']=$authorid;
            $_POST['info']['author']=$author;
            $_POST['info']['status']=1;

            if ($this->db->add($_POST['info']) > 0) {
                //添加接收用户消息
                $_POST['member']['status']=0;
                $_POST['member']['dateline']=$_POST['info']['dateline'];
                $mid=$this->db->field('mid')->limit(1)->order('mid desc')->select();
                $_POST['member']['mid']=$mid[0]['mid'];
                foreach($arr as $message){
                    $_POST['member']['uid']=$message['uid'];
                    $this->member_db->add($_POST['member']);
                }
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("messages", $this->db->select());
            $this->display();
        }
    }

    public function edit() {
        $mid = $_GET['mid'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('mid' => $_POST['mid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/message/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/message/index');
            }
        } else {
            if (empty($mid)) {
                $this->error('异常操作！', __MODULE__ . '/message/index');
            }

            $message = $this->db->getmessage($mid);
            $this->assign('mid', $mid);
            $this->assign("message", $message);
            $this->display();
        }
    }

    public function del() {
        $mid = intval($_GET['mid']);
        if (empty($mid)) {
            $this->error('异常操作！', __MODULE__ . '/message/index');
        }
        $result = $this->db->where("mid = %d", $mid)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/message/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/message/index');
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


}
