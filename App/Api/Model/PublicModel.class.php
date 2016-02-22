<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Home\Model;
use Think\Model;

class PublicModel extends Model {
    protected $tableName = 'student';

    public function studentsList() {
        $students = $this->order("sid desc")->select();
        return $students;
    }

    public function agent_studentsList($aid) {
        $students = $this->where('aid=47')->order("sid desc")->select();
        return $students;
    }

    public function checkstudent($account,$password) {
        $student = $this->where('phone = "%s" and password = "%s" ',$account,$password)->order("sid desc")->select();
        return $student;
    }

    public function addstudent() {
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

    public function getstudent($sid) {
        $student = $this->find($sid);
        return $student;
    }

    public function editstudent($uid) {
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

    public function delstudent($sid) {
        $sid = $_GET['sid'];
        return $this->where("sid = %d", $sid)->delete();
    }

    /*
    删除会员
    */
    public function drop_students( $uid ) {
        $result = $this->where("uid = %d", $uid)->delete();
        return $result;
    }
}