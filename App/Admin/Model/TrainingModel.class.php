<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class TrainingModel extends Model {
    protected $tableName = 'training';

    //获取训练记录数据
    public function training_detail_list() {
        $training_details = $this->table('xy_training t, xy_member_training_details d, xy_student s')
            ->where('d.tid = t.tid and d.sid = s.sid')
            ->field('d.id as id, d.sid as sid,d.tid as tid, d.eid as eid,d.mid as mid,d.dateline as dateline,d.status as status,
            t.title as title,s.realname as realname,
            d.left_train_number as left_train_number,d.right_train_number as right_train_number,d.left_train_length as left_train_length,d.right_train_length as right_train_length,
            d.left_massage_number as left_massage_number,d.right_massage_number as right_massage_number,d.left_massage_length as left_massage_length,d.right_massage_length as right_massage_length')
            ->order('d.id desc');
        return $training_details;
    }

    //获取用户训练完成记录数据
    public function training_list() {
        $trainings = $this->table('xy_training t, xy_member_training d, xy_student s')
            ->where('d.tid = t.tid and d.sid = s.sid')
            ->field('d.id as id, d.sid as sid,d.tid as tid, d.eid as eid,d.mid as mid,d.update_time as update_time,d.status as status,
            t.title as title,s.realname as realname,
            d.training_days as training_days,d.all_training_days as all_training_days,d.all_completion as all_completion,d.today_completion as today_completion')
            ->order('d.id desc');
        return $trainings;
    }



}