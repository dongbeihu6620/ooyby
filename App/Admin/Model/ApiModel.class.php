<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class ApiModel extends Model {
    protected $tableName = 'vision';

    public function visionslist() {
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid')
            ->field('v.vid as vid, s.sid as sid,v.left_eye as left_eye, v.right_eye as right_eye,v.add_time as add_time,s.nickname as nickname,s.realname as realname,s.gender as gender,s.age as age,s.school as school,s.grade as grade,s.classes as classes,v.status as status')
            ->order('v.vid desc');
        return $visions;
    }

    public function addvision() {
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

    public function getvision($vid) {
        $vision = $this->find($vid);
        return $vision;
    }

    public function getstudent($sid) {//写该方法
        $student = D("student")->where('sid = $sid')->select();
        return $student;
    }

    public function editvision($vid) {
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

    public function delvision($vid) {
        $vid = $_GET['vid'];
        return $this->where("vid = %d", $vid)->delete();
    }

    public function lefteyelist($sid) {
        $sid = $_GET['sid'];
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid and s.sid = %d', $sid)
            ->field('v.left_eye as left_eye')
            ->order('v.vid asc')
            ->limit(7)
            ->select();
        $array=array();
        foreach($visions as $v){
            $array[]=$v['left_eye'];
        }
        return $array;
    }

    public function righteyelist($sid) {
        $sid = $_GET['sid'];
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid and s.sid = %d', $sid)
            ->field('v.right_eye as right_eye')
            ->order('v.vid asc')
            ->select();
        $array=array();
        foreach($visions as $v){
            $array[]=$v['right_eye'];
        }
        return $array;
    }

    /*
    删除会员
    */
    public function drop_visions( $vid ) {
        $result = $this->where("vid = %d", $vid)->delete();
        return $result;
    }
}