<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class VisionModel extends Model {
    protected $tableName = 'vision';

    public function visionslist($map) {
        $visions = $this->table('xy_vision v,xy_student s,xy_classes c,xy_school l')
            ->where('v.sid = s.sid and c.id = s.classes and l.id = s.school')
            ->where($map)
            ->field('v.vid as vid, v.left_eye as left_eye, v.right_eye as right_eye,v.add_time as add_time,v.status as status,
            s.realname as realname,l.name as school,c.grade as grade,c.classes as classes')
            ->order('v.vid desc');
        return $visions;
    }

    public function visionlist() {
        $visions = $this->table('xy_vision v,xy_student s,xy_classes c,xy_school l')
            ->where('v.sid = s.sid and c.id = s.classes and l.id = s.school')
            ->field('v.vid as vid, v.left_eye as left_eye, v.right_eye as right_eye,v.add_time as add_time,v.status as status,
            s.realname as realname,l.name as school,c.grade as grade,c.classes as classes')
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

    //左眼视力
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

    //右眼视力
    public function righteyelist($sid) {
        $sid = $_GET['sid'];
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid and s.sid = %d', $sid)
            ->field('v.right_eye as right_eye')
            ->order('v.vid asc')
            ->limit(7)
            ->select();
        $array=array();
        foreach($visions as $v){
            $array[]=$v['right_eye'];
        }
        return $array;
    }

    //双眼视力
    public function eyeslist($sid) {
        $sid = $_GET['sid'];
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid and s.sid = %d', $sid)
            ->field('v.right_eye as right_eye,v.left_eye as left_eye,v.add_time as add_time')
            ->order('v.vid asc')
            ->limit(20)
            ->select();
        return $visions;
    }
    /*
    删除会员
    */
    public function drop_visions( $vid ) {
        $result = $this->where("vid = %d", $vid)->delete();
        return $result;
    }
}