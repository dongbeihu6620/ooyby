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