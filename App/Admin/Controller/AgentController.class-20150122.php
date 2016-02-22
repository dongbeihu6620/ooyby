<?php


namespace Admin\Controller;
use Admin\Controller\CommonController;

/**
 * 栏目操作类
 */
class AgentController extends CommonController {
    private $db, $model_db;
    function __construct() {
        parent::__construct();
        $this->db = D("Agent");
        $this->model_db = D('Model');
    }

    public function index() {
        load('extend');
        $list = array();
        $list = list_to_tree($this->db->agent_list(),'aid','parentid');
        $agents=$this->findChild($list);
        $this->assign('agents',$agents);
        $this->display();
    }

    protected  function findChild($arr){
        static $tree=array();
        foreach ($arr as $key=>$val){
            $tree[]=$val;
            if (isset($val['_child'])){
                $this->findChild($val['_child']);
            }
        }
        return $tree;
    }

    public function add() {
        if(IS_POST) {
            $this->checkToken();
            $data = $_POST['info'];
            $data['password'] = md5($_POST['info']['password']);
            $data['child'] = 0;
            if ($data['parentid']) {
                $parentagent = $this->db->where("aid = %d",$data['parentid'])->find();
                if (!$parentagent) {
                    $this->error('上级代理不存在');
                    exit(0);
                }
                $data['level'] = $parentagent['level'] + 1;
                $data['arrparentid'] = $parentagent['arrparentid'] . "," .$parentagent['aid'];
                if($agentid = $this->db->add($data)){
                    if ($this->db->where("aid = %d",$agentid)->save(array('arrchildid' => $agentid)) === false) {
                        $this->db->rollback();
                        $this->error('操作失败！');
                    }
                    $data_parent = array();
                    $data_parent['child'] = 1;
                    $data_parent['arrchildid'] = $parentagent['arrchildid'] . "," . $agentid;
                    if ($this->db->where("aid = %d",$data['parentid'])->save($data_parent) === false) {
                        $this->db->rollback();
                        $this->error('操作失败！');
                    };
                    $this->db->commit();
                    $this->success('操作成功！',__MODULE__.'/Agent/index');
                } else {
                    $this->db->rollback();
                    $this->error('操作失败！');
                }
            } else {
                $data['level'] = 1;
                $data['arrparentid'] = "0";
                if ($agentid = $this->db->add($data)) {
                    $this->db->where("aid = %d",$agentid)->save(array('arrchildid' => $agentid));
                    $this->success('操作成功！',__MODULE__.'/Agent/index');
                } else {
                    $this->error('操作失败！');
                }
            }
        } else {
            $aid = (isset($_GET['aid']) ? intval($_GET['aid']) : 0);
            if ($aid) {
                $agent = $this->db->where(array('aid' => $aid))->find();
                $this->assign('agent',$agent);
            }
            $list = array();
            $list = list_to_tree($this->db->agent_list(),'aid','parentid');
            $agents=$this->findChild($list);
            $this->assign('agents',$agents);
            $this->assign('aid',$aid);
            //$this->assign("model_list", $this->model_db->where(array('siteid'=>$this->siteid, 'typeid' => 0))->select());
            $this->display();
        }
    }

    public function edit() {
        if (IS_POST) {
            $this->checkToken();
            $cat = $this->db->where("aid = %d",$_POST['aid'])->find();
            $data = $_POST['info'];
            // 是否修改父栏目
            if ($data['parentid'] == $cat['parentid']) {
                // echo "没有修改父栏目";
                if ($this->db->where("aid = %d",$_POST['aid'])->save($data) !== false) {
                    // echo $this->db->getLastSql();
                    $this->success('操作成功！',__MODULE__.'/agent/index');
                } else {
                    $this->error('操作失败!');
                }
            } else {
                if ($data['parentid']) {
                    // 更新 'parentid' and 'arrparentid'
                    $parentcat = $this->db->where("aid = %d",$data['parentid'])->find();
                    if (!$parentcat) {
                        $this->error('上级代理不存在');
                        exit(0);
                    }
                    $data['arrparentid'] = $parentcat['arrparentid'] . "," .$parentcat['id'];
                    $data['level'] = $parentcat['level'] + 1;
                    $this->db->startTrans();
                    if ($this->db->where("aid = %d",$_POST['aid'])->save($data) !== false) {
                        /* 更新原上级代理 */
                        $origin_parentcat = $this->db->where("aid = %d",$cat['parentid'])->find();
                        if ($origin_parentcat) {
                            $arrchildid = explode(',', $origin_parentcat['arrchildid']);
                            foreach ($arrchildid as $key => $value) {
                                if ($value == $cat['aid']) {
                                    unset($arrchildid[$key]);
                                    break;
                                }
                            }
                            $arrchildid = join(',',$arrchildid);
                            $origin_parent_data = array('arrchildid' => $arrchildid);
                            if ($arrchildid == $origin_parentcat['aid']) {
                                $origin_parent_data['child'] = 0;
                            }
                            if ($this->db->where("aid = %d",$origin_parentcat['aid'])->save($origin_parent_data) === false) {
                                $this->db->rollback();
                                $this->error("更新原上级代理失败!");
                            };
                        }
                        /* 更新现上级代理 */
                        $data_parent = array('child' => 1, 'arrchildid' => $parentcat['arrchildid'] . "," . $cat['aid']);
                        if ($this->db->where("aid = %d",$data['parentid'])->save($data_parent) !== false) {
                            // echo '更新现父栏目成功';
                            $this->db->commit();
                            $this->success('操作成功！',__MODULE__.'/agent/index');
                        } else {
                            $this->db->rollback();
                            $this->error("更新现上级代理失败!");
                        }
                    } else {
                        $this->db->rollback();
                        $this->error("代理信息更新失败!");
                    }
                } else {
                    $data['level'] = 1;
                    $data['arrparentid'] = "0";
                    $this->db->startTrans();
                    if ($this->db->where("aid = %d",$_POST['aid'])->save($data) !== false) {
                        $origin_parentcat = $this->db->where("aid = %d",$cat['parentid'])->find();
                        $arrchildid = explode(',', $origin_parentcat['arrchildid']);
                        foreach ($arrchildid as $key => $value) {
                            if ($value == $cat['aid']) {
                                unset($arrchildid[$key]);
                                break;
                            }
                        }
                        $arrchildid = join(',',$arrchildid);
                        $origin_parent_data = array('arrchildid' => $arrchildid);
                        if ($arrchildid == $origin_parentcat['aid']) {
                            $origin_parent_data['child'] = 0;
                        }
                        if ($this->db->where("aid = %d",$origin_parentcat['aid'])->save($origin_parent_data) !== false) {
                            $this->db->commit();
                            $this->success('操作成功！',__MODULE__.'/agent/index');
                        } else {
                            $this->db->rollback();
                            $this->error("更新原上级代理失败!");
                        }
                    } else {
                        $this->db->rollback();
                        $this->error("代理信息更新失败!");
                    }
                }
            }
        } else {
            $aid = (isset($_GET['aid']) ? intval($_GET['aid']) : 0);
            $cats = list_to_tree($this->db->agent_list(),'aid','parentid');
            $agents=$this->findChild($cats);
            $agent = $this->db->where("aid = %d",$aid)->find();
            $this->assign('agent',$agent);
            $this->assign('aid',$aid);
            $this->assign('agents',$agents);
            $this->display();
        }
    }

    public function del() {
        if ($this->db->where("parentid = %d",$_GET['aid'])->count()) {
            $this->error('请先删除子代理信息！');
        }
        if ($this->db->where("aid = %d", $_GET['aid'])->delete() !== false) {
            $this->success('删除成功!');
        } else {
            $this->error('删除失败!');
        }
    }

    public function listorder() {
        if (isset($_POST['listorder']) && is_array($_POST['listorder'])) {
            $listorder = $_POST['listorder'];
            foreach ($listorder as $k => $v) {
                $this->db->where(array('aid'=>$k))->save(array('listorder'=>$v));
            }
        }
        $this->success('排序成功');
    }

    /**
     * 快速进入搜索
     */
    public function public_ajax_search() {
        if($_GET['catname']) {
            if(preg_match('/([a-z]+)/i',$_GET['catname'])) {
                $field = 'letter';
                $catname = strtolower(trim($_GET['catname']));
            } else {
                $field = 'catname';
                $catname = trim($_GET['catname']);
            }
            $result = $this->db->where("{$field} LIKE('%{$catname}%') AND siteid={$this->siteid} AND child=0")->field('id,type,catname,letter')->limit(10)->select();
            echo json_encode($result);
        }
    }

    /**
     * ajax检查栏目是否存在
     */
    public function public_check_catdir() {
        if (!$_GET['catdir']) exit("0");
        $catdir = $_GET['catdir'];
        if (isset($_GET['catid'])) {
            $cat = $this->db->where("catdir = '%s' and id != %d",$catdir,$_GET['catid'])->find();
        } else {
            $cat = $this->db->where("catdir = '%s'",$catdir)->find();
        }
        if($cat) {
            exit("0");
        } else {
            exit("1");
        }
    }
}