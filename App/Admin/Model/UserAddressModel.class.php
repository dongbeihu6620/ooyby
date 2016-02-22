<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class UserAddressModel extends Model {
    public function addressList() {
        $address = $this->table('xy_user_address a, xy_member m')->where('a.uid = m.uid')
            ->field('a.id as id, a.uid as uid,a.name as name, a.mobile as mobile,a.province as province,a.city as city,a.district as district,a.address as address,a.zipcode as zipcode,a.is_default as is_default,a.status as status,m.realname as realname')
            ->order('a.id desc');
        return $address;
    }

    public function getaddress($id) {
        $address = $this->find($id);
        return $address;
    }

    public function deladdress($id) {
        return $this->where("id = %d", $id)->delete();
    }

}