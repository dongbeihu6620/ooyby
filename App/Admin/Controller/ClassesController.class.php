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
class ClassesController extends CommonController {
    private $db,$school_db;
    function __construct() {
        parent::__construct();
        $this->db = D("Classes");
        $this->school_db = D("School");
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
        $classes = $this->db->limit($page->firstRow.','.$page->listRows)->classes_list()->select();

        $this->assign('page',$show);
        $this->assign('classes', $classes);
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
            $this->assign("classes", $this->school_db->select());
            $this->display();
        }
    }

    public function edit() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Classes/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Classes/index');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/Classes/index');
            }
            $schools = $this->school_db->order('id desc')->select();
            $classes = $this->db->getclasses($id);

            $this->assign('id', $id);
            $this->assign('sid', $classes['sid']);
            $this->assign("classes", $classes);
            $this->assign("shcools", $schools);
            $this->display();
        }
    }

    public function del() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('异常操作！', __MODULE__ . '/Classes/index');
        }
        $result = $this->db->where("id = %d", $id)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Classes/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Classes/index');
        }
    }


}
