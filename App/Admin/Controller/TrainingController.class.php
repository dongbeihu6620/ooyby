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
 * 训练计划管理
 */
class TrainingController extends CommonController {
    private $db, $training_db, $detail_db;
    function __construct() {
        parent::__construct();
        $this->db = D("training");
        $this->training_db = D('member_training');
        $this->detail_db = D('member_training_details');
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
        $trainings = $this->db->limit($page->firstRow.','.$page->listRows)->order('tid desc')->select();

        $this->assign('page',$show);
        $this->assign('trainings', $trainings);
        $this->display();
    }

    //添加训练计划
    public function add() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            if ($this->db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("trainings", $this->db->select());
            $this->display();
        }
    }

    //修改训练计划
    public function edit() {
        $tid = $_GET['tid'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('tid' => $_POST['tid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/training/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/training/index');
            }
        } else {
            if (empty($tid)) {
                $this->error('异常操作！', __MODULE__ . '/training/index');
            }
            $training = $this->db->find($tid);
            $this->assign('tid', $tid);
            $this->assign("training", $training);
            $this->display();
        }
    }

    //删除训练计划
    public function del() {
        $tid = intval($_GET['tid']);
        if (empty($tid)) {
            $this->error('异常操作！', __MODULE__ . '/training/index');
        }
        $result = $this->db->where("tid = %d", $tid)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/training/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/training/index');
        }
    }

    //用户训练记录列表
    public function training_detail() {
        $count = $this->detail_db->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $trainings =$this->db->training_detail_list()->limit($page->firstRow.','.$page->listRows)->select();
        //$trainings =$this->detail_db->training_detail_list->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('trainings', $trainings);
        $this->display();
    }

    //添加用户训练记录
    public function add_training_detail() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['dateline']=strtotime(date('y-m-d h:i:s',time()));
            $this->detail_db->startTrans();//如果用户没有接受训练时，回滚添加的训练记录
            $add_detail=$this->detail_db->add($_POST['info']);
            if ($add_detail > 0) {//添加记录成功
                $training_detail=$this->detail_db->limit(1)->order('id desc')->select();
                //用户是否已经接受训练方案，未接受的话需要用户先接受才能进行训练
                $add_training=$this->training_db->where("tid = %d and sid = %d",$training_detail[0]["tid"],$training_detail[0]["sid"])->order('id desc')->limit(1)->select();
                if (!empty($add_training)) {//用户接受过该训练计划,更新连续训练天数、总训练天数、当日完成情况、总体完成情况
                    if (date("Y-m-d", strtotime("-1 day")) == date("Y-m-d", $add_training[0]["update_time"])) {//如果昨天已经更新过，则training_days+1
                        $this->training_db->where('id = %d',$add_training[0]['id'])->setInc('training_days'); // 用户连续训练天数加1
                    }
                    if (round(($training_detail[0]["dateline"]-$add_training[0]["update_time"])/3600/24) >=2) {//如果最近没有更新过，则training_days为0
                        $_POST['training_days']['training_days']=0;
                        $this->training_db->where('id = %d',$add_training[0]['id'])->save($_POST['training_days']); // 用户连续训练天数为0
                    }
                    if (date("Y-m-d", $add_training[0]["update_time"]) != date("Y-m-d", $training_detail[0]["dateline"])){//如果添加的记录与更新时间不是同一天，则all_training_days+1
                        $this->training_db->where('id = %d',$add_training[0]['id'])->setInc('all_training_days'); //用户连续训练天数加1
                    }

                    $length=$this->training_length($add_training[0]['tid']);//获取当前训练方案的每天训练时长和总体训练时长
                    $now_length=$this->now_training_length($training_detail[0]['id']);//获取当前训练记录的训练时长

                    if (!empty($length) || !empty($now_length)){
                        $_POST['training']['update_time'] = $training_detail[0]['dateline'];
                        $this->training_db->where('id = %d',$add_training[0]['id'])->save($_POST['training']); //更新最新时间
                        $_POST['training_length']['today_completed']=$now_length['day_length'];//更新今日完成情况
                        if (date("Y-m-d", $add_training[0]["update_time"]) == date("Y-m-d", $training_detail[0]["dateline"])) {//如果更新时间为今天，扣减今天增加的完成时长
                            $_POST['training_length']['all_completed'] = $add_training[0]['all_completed'] + $now_length['day_length'] - $add_training[0]['today_completed'];//累计完成情况
                        }
                        else{
                            $_POST['training_length']['all_completed'] = $add_training[0]['all_completed'] + $now_length['day_length'];//累计完成情况
                        }
                        $_POST['training_length']['today_completion']=$now_length['day_length']/$length['day_length'];//今天完成情况
                        $_POST['training_length']['all_completion']=$_POST['training_length']['all_completed']/$length['all_length'];//总体完成情况
                        $this->training_db->where('id = %d',$add_training[0]['id'])->save($_POST['training_length']);
                    }
                    else{
                        $this->detail_db->rollback();
                        $this->error('不存在该训练计划时长！');
                    }
                    $this->detail_db->commit();
                    $this->success('操作成功！', "training_detail");
                }
                else{
                    $this->detail_db->rollback();
                    $this->error('用户未接受该训练计划！');
                }
            } else {
                $this->detail_db->rollback();
                $this->error('操作失败！');
            }
        } else {
            $trainings = $this->db->order('tid desc')->select();
            $this->assign("trainings", $trainings);
            $this->display();
        }
    }

    //当天训练任务及总训练任务总时长
    public function training_length($tid) {
        if (empty($tid)) {
            $this->error('训练计划不能为空');
        }
        $result = $this->db->where("tid = %d", $tid)->select();
        if ($result) {
            $length['day_length'] = $result[0]['left_train_number']*$result[0]['left_train_length']
                                   +$result[0]['right_train_number']*$result[0]['right_train_length']
                                   +$result[0]['left_massage_number']*$result[0]['left_massage_length']
                                   +$result[0]['right_massage_number']*$result[0]['right_massage_length'];
            $length['all_length'] = $length['day_length'] * $result[0]['date_length'];
            return $length;
        } else {
            $this->error('不存在该训练计划');
        }
    }

    //当前训练总时长
    public function now_training_length($id) {
        if (empty($id)) {
            $this->error('训练记录详情不能为空');
        }
        $result = $this->detail_db->where("id = %d", $id)->select();
        if ($result) {
            $length['day_length'] = $result[0]['left_train_number']*$result[0]['left_train_length']
                +$result[0]['right_train_number']*$result[0]['right_train_length']
                +$result[0]['left_massage_number']*$result[0]['left_massage_length']
                +$result[0]['right_massage_number']*$result[0]['right_massage_length'];
            return $length;
        } else {
            $this->error('不存在该训练记录');
        }
    }

    //修改用户训练记录
    public function edit_training_detail() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->detail_db->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/training/training_detail');
            } else {
                $this->error('操作失败！', __MODULE__ . '/training/training_detail');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/training/training_detail');
            }
            $training = $this->detail_db->find($id);
            $this->assign('tid', $training['tid']);
            $this->assign('id', $id);
            $trainings = $this->db->order('tid desc')->select();
            $this->assign("trainings", $trainings);
            $this->assign("training", $training);
            $this->display();
        }
    }

    //删除用户训练记录
    public function del_training_detail() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('异常操作！', __MODULE__ . '/training/training_detail');
        }
        $result = $this->detail_db->where("id = %d", $id)->delete();
        if ($result) {
            $this->success('操作成功！', __MODULE__ . '/training/training_detail');
        } else {
            $this->error('操作失败！', __MODULE__ . '/training/training_detail');
        }
    }

    //用户训练完成情况列表
    public function training() {
        $count = $this->training_db->count();
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $trainings =$this->db->training_list()->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();

        $this->assign('page',$show);
        $this->assign('trainings', $trainings);
        $this->display();
    }

    //添加用户训练完成情况
    public function add_training() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['update_time'] = strtotime(date('y-m-d h:i:s',time()));
            $_POST['info']['status'] = 1;
            if ($this->training_db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "training");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $trainings = $this->db->order('tid desc')->select();
            $this->assign("trainings", $trainings);
            $this->display();
        }
    }

    //修改用户训练完成情况
    public function edit_training() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['update_time'] = strtotime(date('y-m-d h:i:s',time()));
            if ($this->training_db->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/training/training');
            } else {
                $this->error('操作失败！', __MODULE__ . '/training/training');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/training/training');
            }

            $training = $this->training_db->find($id);
            $trainings = $this->db->order('tid desc')->select();
            $this->assign('tid', $training['tid']);
            $this->assign('id', $id);
            $this->assign("trainings", $trainings);
            $this->assign("training", $training);
            $this->display();
        }
    }

    //删除用户训练完成情况
    public function del_training() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('异常操作！', __MODULE__ . '/training/training');
        }
        $result = $this->training_db->where("id = %d", $id)->delete();
        if ($result) {
            $this->success('操作成功！', __MODULE__ . '/training/training');
        } else {
            $this->error('操作失败！', __MODULE__ . '/training/training');
        }
    }

}
