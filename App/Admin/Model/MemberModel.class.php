<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class MemberModel extends Model {
    protected $tableName = 'member';

    public function memberList() {
        $members = $this->order("uid desc")->select();
        return $members;
    }

    public function addmember() {
        $datas['module'] = trim($_POST['module']);
        $datas['action'] = trim($_POST['action']);
        $datas['title'] = trim($_POST['title']);
        $datas['params'] = trim($_POST['params']);
        $datas['status'] = trim($_POST['status']);
        $datas['remark'] = trim($_POST['remark']);
        $datas['sort'] = trim($_POST['sort']);
        $datas['pid'] = $_POST['pid'];
        return $this->add($datas);
    }

    public function getmember($uid) {
        $member = $this->find($uid);
        return $member;
    }

    public function editmember($uid) {
        $uid = $_POST['nid'];
        $datas['module'] = trim($_POST['module']);
        $datas['action'] = trim($_POST['action']);
        $datas['title'] = trim($_POST['title']);
        $datas['params'] = trim($_POST['params']);
        $datas['status'] = trim($_POST['status']);
        $datas['remark'] = trim($_POST['remark']);
        $datas['sort'] = trim($_POST['sort']);
        $datas['pid'] = $_POST['pid'];
        return $this->where("id = %d", $uid)->save($datas);
    }

    public function delmember($uid) {
        $uid = $_GET['uid'];
        return $this->where("uid = %d", $uid)->delete();
    }

    /*
    删除会员
    */
    public function drop_members( $uid ) {
        $result = $this->where("uid = %d", $uid)->delete();
        return $result;
    }
}