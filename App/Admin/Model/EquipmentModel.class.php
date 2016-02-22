<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class EquipmentModel extends Model {
    protected $tableName = 'equipment';

    //获取设备信息
    public function equipment_list() {
        $equipments = $this->table('xy_equipment e, xy_member u, xy_student s')
            ->where('u.uid = e.uid and e.sid = s.sid')
            ->field('e.eid as eid, e.uid as uid,e.sid as sid, e.num as num,e.type as type,e.name as name,e.status as status,
            e.activition_time as activition_time,e.update_time as update_time,e.version as version,
            u.realname as uname,s.realname as sname')
            ->order('e.eid desc');
        return $equipments;
    }

}