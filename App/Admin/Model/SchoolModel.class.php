<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class SchoolModel extends Model {
    protected $tableName = 'school';
    
    public function getschool($id) {
        $school = $this->find($id);
        return $school;
    }





}