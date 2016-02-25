<?php
/**
 * Created by PhpStorm.
 * User: xueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */

namespace Admin\Controller;
use Admin\Controller\CommonController;

/**
 * 设备管理
 */
class EquipmentController extends CommonController {

    private $db,$detail;
    function __construct() {
        parent::__construct();
        $this->db = D("Equipment");
        $this->detail = D("EquipmentDetail");
    }

    public function index() {

        $count = $this->db->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $equipments = $this->db->equipment_list()->limit($page->firstRow.','.$page->listRows)->select();

        //$day_list = array('2016-02-21','2016-02-20','2016-02-19','2016-02-17');
        //$days = $this->continue_day($day_list);
        $this->assign('page',$show);
        $this->assign('equipments', $equipments);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $_POST['info']['status']= 0;
            $_POST['info']['activation_code']=$this->getRandomString(10);
            $this->checkToken();
            if ($this->db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("members", D("member")->order('uid desc')->select());
            $this->assign("students", D("student")->order('sid desc')->select());
            $this->assign("schools", D("school")->order('id desc')->select());
            $this->assign("agents", D("user")->order('id')->where('level > 0')->select());
            $this->assign("equipments", $this->db->select());
            $this->display();
        }
    }

    public function edit() {
        $eid = $_GET['eid'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('eid' => $_POST['eid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Equipment/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Equipment/index');
            }
        } else {
            if (empty($eid)) {
                $this->error('异常操作！', __MODULE__ . '/Equipment/index');
            }

            $equipment = $this->db->find($eid);

            $this->assign('eid', $eid);
            $this->assign('uid', $equipment['uid']);
            $this->assign('sid', $equipment['sid']);
            $this->assign('cid', $equipment['cid']);
            $this->assign('id', $equipment['aid']);
            $this->assign("equipment", $equipment);
            $this->assign("members", D("member")->order('uid desc')->select());
            $this->assign("students", D("student")->order('sid desc')->select());
            $this->assign("schools", D("school")->order('id desc')->select());
            $this->assign("agents", D("user")->order('id')->where('level > 0')->select());
            $this->display();
        }
    }

    public function del() {
        $eid = intval($_GET['eid']);
        if (empty($eid)) {
            $this->error('异常操作！', __MODULE__ . '/Equipment/index');
        }
        $result = $this->db->where("eid = %d", $eid)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Equipment/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Equipment/index');
        }
    }

    //添加终端使用记录
    public function add_detail() {
        if (IS_POST) {
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            $this->checkToken();
            if ($this->detail->add($_POST['info']) > 0) {
                //更新最新访问时间
                $this->success('操作成功！', "detail_index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            //$this->assign("equipments", $this->db->select());
            $this->display();
        }
    }

    //终端使用记录列表
    public function detail_index() {
        $count = $this->detail->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $equipment_details = $this->db->limit($page->firstRow.','.$page->listRows)->equipment_detail_list()->select();

        $this->assign('page',$show);
        $this->assign('equipment_details', $equipment_details);
        $this->display();
    }

    //修改终端使用记录
    public function edit_detail() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['dateline']=strtotime($_POST['info']['dateline']);
            if ($this->detail->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Equipment/detail_index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Equipment/detail_index');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/Equipment/detail_index');
            }

            $equipment = $this->detail->find($id);
            $this->assign('id', $id);
            $this->assign("equipment", $equipment);
            $this->display();
        }
    }

    //设备连续使用天数
    function continue_day($day_list){
        $continue_day = 1 ;//连续天数
        if(count($day_list) >= 1)    {
            for ($i=1; $i<=count($day_list); $i++){
                if((abs((strtotime(date('Y-m-d')) - strtotime($day_list[$i-1])) / 86400)) == $i ){
                    $continue_day = $i+1;
                } else {
                    break;
                }
            }
        }
        return $continue_day;    //输出连续几天
    }

    //设备使用记录统计
    function detail_count(){
        $count = $this->detail->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        //$detail_count = $this->db->limit($page->firstRow.','.$page->listRows)->detail_countbyeid()->select();
        $detail_count = $this->db->detail_countdau();
        dump($detail_count);
        exit();

        $this->assign('page',$show);
        $this->assign('detail_count', $detail_count);
        $this->display();

    }

    //随机生成数字和字母组合字符串
    function getRandomString($len, $chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    //激活设备
    public function check_activation() {
        if (IS_POST) {
            $code = $_POST['code'];
            if (empty($code)) {
                $this->error('激活码不能为空！', __MODULE__ . '/Equipment/index');
            }
            $this->checkToken();
            $equipment = $this->db->where('activation_code = "%s"', $code)->select();
            if (!empty($equipment)) {
                if ($equipment[0]['status']== 1){
                    $this->error('该设备之前已经激活过，请更换激活码！');
                }
                $_POST['info']['activation_time']=strtotime(date('y-m-d h:i:s',time()));
                $_POST['info']['status']= 1;
                if ($this->db->where(array('eid' => $equipment[0]['eid']))->save($_POST['info']) !== false) {
                    $this->success('激活成功！', __MODULE__ . '/Equipment/index');
                } else {
                    $this->error('激活失败！', __MODULE__ . '/Equipment/index');
                }
            } else {
                $this->error('激活码不存在！');
            }
        } else {
            $this->display();
        }
    }

    //取消激活设备
    public function check_unactivation() {
        if (IS_POST) {
            $code = $_POST['code'];
            if (empty($code)) {
                $this->error('激活码不能为空！', __MODULE__ . '/Equipment/index');
            }
            $this->checkToken();
            $equipment = $this->db->where('activation_code = "%s"', $code)->select();
            if (!empty($equipment)) {
                if ($equipment[0]['status']== 0){
                    $this->error('该设备还未激活过，无法取消激活！');
                }
                $_POST['info']['activation_time']=0;
                $_POST['info']['status']= 0;
                if ($this->db->where(array('eid' => $equipment[0]['eid']))->save($_POST['info']) !== false) {
                    $this->success('取消激活成功！', __MODULE__ . '/Equipment/index');
                } else {
                    $this->error('取消激活失败！', __MODULE__ . '/Equipment/index');
                }
            } else {
                $this->error('激活码不存在！');
            }
        } else {
            $this->display();
        }
    }

    //终端概况-视力测试机
    function equipment_count(){
        //所有视力测试仪设备数量
        $count['all_num'] = $this->db->where('type = 2')->count();
        //已激活视力测试仪设备数量
        $count['activation_num'] = $this->db->where('type = 2 and status = 1')->count();
        $students = $this->db->vision_type_list()->group('e.sid')->select();
        //使用视力测试仪测视力的学生数量
        $count['students'] = count($students);
        //使用视力测试仪测试的总数量
        $count['vision_num'] = $this->db->vision_type_list()->count();

        $this->assign('count', $count);
        $this->display();

    }

    /**
     * ajax检查设备号是否存在
     */
    public function check_num() {
        if (!$_GET['num']) exit("0");
        $num = $_GET['num'];
        $nums = $this->db->where("num = '%s'",$num)->find();
        if($nums) {
            exit("0");
        } else {
            exit("1");
        }
    }


}
