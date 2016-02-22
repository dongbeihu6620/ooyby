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
 * 设备管理
 */
class EquipmentController extends CommonController {

    private $db,$detail;
    function __construct() {
        parent::__construct();
        $this->db = D("Equipment");
        $this->detail = D("EquipmentDetail");
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
        $equipments = $this->db->equipment_list()->limit($page->firstRow.','.$page->listRows)->select();

        $this->assign('page',$show);
        $this->assign('equipments', $equipments);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $_POST['info']['status']= 0;
            $this->checkToken();
            if ($this->db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("equipments", $this->db->select());
            $this->display();
        }
    }

    public function edit() {
        $eid = $_GET['eid'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('eid' => $_POST['eid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Equipment/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Equipment/index');
            }
        } else {
            if (empty($eid)) {
                $this->error('异常操作！', __MODULE__ . '/Equipment/index');
            }

            $equipment = $this->db->find($eid);
            $this->assign('eid', $eid);
            $this->assign("equipment", $equipment);
            $this->display();
        }
    }

    public function del() {
        $eid = intval($_GET['eid']);
        if (empty($eid)) {
            $this->error('异常操作！', __MODULE__ . '/Equipment/index');
        }
        $result = $this->db->where("eid = %d", $eid)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Equipment/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Equipment/index');
        }
    }

    //添加终端使用记录
    public function add_detail() {
        if (IS_POST) {
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            $this->checkToken();
            if ($this->detail->add($_POST['info']) > 0) {
                //更新最新访问时间
                $this->success('操作成功！', "detail_index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            //$this->assign("equipments", $this->db->select());
            $this->display();
        }
    }

    //终端使用记录列表
    public function detail_index() {
        $count = $this->detail->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $equipment_details = $this->detail->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();

        $this->assign('page',$show);
        $this->assign('equipment_details', $equipment_details);
        $this->display();
    }

    //修改终端使用记录
    public function edit_detail() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->detail->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Equipment/detail_index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Equipment/detail_index');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/Equipment/detail_index');
            }

            $equipment = $this->detail->find($id);
            $this->assign('id', $id);
            $this->assign("equipment", $equipment);
            $this->display();
        }
    }



}
