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
class SchoolController extends CommonController {
    private $db;
    function __construct() {
        parent::__construct();
        $this->db = D("School");
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
        $schools = $this->db->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();

        $this->assign('page',$show);
        $this->assign('schools', $schools);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['status']=1;

            if ($this->db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            //$this->assign("schools", $this->db->select());
            $this->display();
        }
    }

    public function edit() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/School/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/School/index');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/School/index');
            }
            $this->assign("school", D("School")->getschool($id));
            $this->assign('id', $id);
            $this->display();
        }
    }

    public function del() {
        $id = intval($_GET['id']);

        $result = $this->db->where("id = %d", $id)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/School/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/School/index');
        }
    }


}
