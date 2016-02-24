<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class ClassesModel extends Model {
    protected $tableName = 'classes';

    public function classes_list() {
        $classes_details = $this->table('xy_school s, xy_classes c')
            ->where('s.id = c.sid')
            ->field('c.id as id, c.sid as sid,c.grade as grade, c.classes as classes,c.status as status,c.orderlist as orderlist,
            s.name as name')
            ->order('c.id desc');
        return $classes_details;
    }

    public function getclasses($id) {
        $classes = $this->find($id);
        return $classes;
    }

}