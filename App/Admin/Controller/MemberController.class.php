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
class MemberController extends CommonController {
    public function index() {
        // 菜单显示自定义方式
        $members = D("Member")->memberList();
        $this->assign('members', $members);
        $this->display('member:index');
    }

    public function add() {
        if (IS_POST) {
            $this->checkToken();
            if (D("Member")->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("members", D("Member")->memberList());
            $this->display();
        }
    }

    public function edit() {
        $uid = $_GET['uid'];
        if (IS_POST) {
            $this->checkToken();
            if (D("Member")->where(array('uid' => $_POST['uid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Member/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Member/index');
            }
        } else {
            if (empty($uid)) {
                $this->error('异常操作！', __MODULE__ . '/Member/index');
            }
            $this->assign('uid', $uid);
            $this->assign("member", D("Member")->getmember($uid));
            //$this->assign("members", D("Member")->memberList());
            $this->display();
        }
    }

    public function del() {
        $uid = intval($_GET['uid']);
        if (empty($uid)) {
            $this->error('异常操作！', __MODULE__ . '/Member/index');
        }
        $result = D('Member')->delmember($uid);
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Member/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Member/index');
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
