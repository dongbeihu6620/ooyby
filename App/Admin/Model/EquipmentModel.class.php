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
            e.activation_time as activation_time,e.update_time as update_time,e.version as version,e.activation_code as activation_code,
            u.realname as uname,s.realname as sname')
            ->order('e.eid desc');
        return $equipments;
    }

    //获取设备使用记录信息
    public function equipment_detail_list() {
        $equipments = $this->table('xy_equipment e, xy_equipment_detail d')
            ->where('e.eid = d.eid')
            ->field('d.id as id, d.eid as eid,d.use_length as use_length, d.dateline as dateline,
            e.name as name')
            ->order('d.id desc');
        return $equipments;
    }

    //按照设备分组统计使用次数
    public function detail_countbyeid() {
        $equipments = $this->table('xy_equipment_detail')
            ->field('count(*) num')
            ->order('id')
            ->group('eid');
        return $equipments;
    }

    //按照日期分组统计使用次数
    public function detail_countbyday() {
        $equipments = $this->table('xy_equipment_detail')
            ->field("count(*) num,FROM_UNIXTIME(dateline,'%Y-%m-%d') as day")
            ->order('day desc')
            ->group('day');
        return $equipments;
    }

    //按照月份分组统计使用次数
    public function detail_countbymonth() {
        $equipments = $this->table('xy_equipment_detail')
            ->field("count(*) num,FROM_UNIXTIME(dateline,'%Y-%m') as month")
            ->order('month desc')
            ->group('month');
        return $equipments;
    }

    //统计某天DAU
    public function detail_countdau() {
        $equipment = $this->table('xy_equipment_detail')
            ->distinct(true)
            ->field('eid')
            ->select();
        $equipments=count($equipment);
        return $equipments;
    }

    //获取设备信息
    public function vision_type_list() {
        $visions = $this->table('xy_equipment e, xy_vision v')
            ->where('e.eid = v.eid and e.type = 2');
        return $visions;
    }


}