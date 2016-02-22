<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */

namespace Admin\Controller;
use Admin\Controller\CommonController;

/**
 * 会员管理
 */
class UserAddressController extends CommonController {
    private $db;
    function __construct() {
        parent::__construct();
        $this->db = D("UserAddress");
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
        $address = $this->db->addressList()->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();

        $this->assign('page',$show);
        $this->assign('address', $address);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            $_POST['info']['status']=1;
            if ($this->db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->display();
        }
    }

    public function edit() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            if ($this->db->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/UserAddress/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/UserAddress/index');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/UserAddress/index');
            }
            $this->assign('id', $id);
            $this->assign("address", $this->db->getaddress($id));
            $this->display();
        }
    }

    public function del() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('异常操作！', __MODULE__ . '/UserAddress/index');
        }
        $result = $this->db->deladdress($id);
        if ($result) {
            $this->success('操作成功！', __MODULE__ . '/UserAddress/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/UserAddress/index');
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
