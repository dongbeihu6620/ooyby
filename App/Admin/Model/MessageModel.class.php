<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class MessageModel extends Model {
    protected $tableName = 'message';

    public function visionlist() {
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid')
            ->field('v.vid as vid, s.sid as sid,v.left_eye as left_eye, v.right_eye as right_eye,v.add_time as add_time,s.nickname as nickname,s.realname as realname,s.gender as gender,s.age as age,s.school as school,s.grade as grade,s.classes as classes,v.status as status')
            ->order('v.vid desc');
        return $visions;
    }

    public function getmessage($mid) {
        $message = $this->find($mid);
        return $message;
    }

    public function delvision($vid) {
        $vid = $_GET['vid'];
        return $this->where("vid = %d", $vid)->delete();
    }

    public function delmessage($id) {
        $id = $_GET['id'];
        return $this->where("id = %d", $id)->delete();
    }



}