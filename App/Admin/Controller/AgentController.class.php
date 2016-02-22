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
        $this->db = D("User");
        $this->model_db = D('Model');
    }

    public function index() {
        load('extend');
        $agent = D("User")->where('level != 0')->order('arrparentid asc, listorder desc, id asc')->select();
        $list = list_to_tree($agent,'id','parentid');
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
            $data['role_id'] = 3;
            if ($data['parentid']) {
                $parentagent = $this->db->where("id = %d",$data['parentid'])->find();
                if (!$parentagent) {
                    $this->error('上级代理不存在');
                    exit(0);
                }
                $data['level'] = $parentagent['level'] + 1;
                $data['arrparentid'] = $parentagent['arrparentid'] . "," .$parentagent['id'];
                if($agentid = $this->db->add($data)){
                    if ($this->db->where("id = %d",$agentid)->save(array('arrchildid' => $agentid)) === false) {
                        $this->db->rollback();
                        $this->error('操作失败！');
                    }
                    $data_parent = array();
                    $data_parent['child'] = 1;
                    $data_parent['arrchildid'] = $parentagent['arrchildid'] . "," . $agentid;
                    if ($this->db->where("id = %d",$data['parentid'])->save($data_parent) === false) {
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
                    $this->db->where("id = %d",$agentid)->save(array('arrchildid' => $agentid));
                    $this->success('操作成功！',__MODULE__.'/Agent/index');
                } else {
                    $this->error('操作失败！');
                }
            }
        } else {
            $id = (isset($_GET['id']) ? intval($_GET['id']) : 0);
            if ($id) {
                $agent = $this->db->where(array('id' => $id))->find();
                $this->assign('agent',$agent);
            }
            $list = array();
            $agent = D("User")->where('level != 0')->order('arrparentid asc, listorder desc, id asc')->select();
            $list = list_to_tree($agent,'id','parentid');
            $agents=$this->findChild($list);
            $this->assign('agents',$agents);
            $this->assign('id',$id);
            //$this->assign("model_list", $this->model_db->where(array('siteid'=>$this->siteid, 'typeid' => 0))->select());
            $this->display();
        }
    }

    public function edit() {
        if (IS_POST) {
            $this->checkToken();
            $cat = $this->db->where("id = %d",$_POST['id'])->find();
            $data = $_POST['info'];
            // 是否修改父栏目
            if ($data['parentid'] == $cat['parentid']) {
                // echo "没有修改父栏目";
                if ($this->db->where("id = %d",$_POST['id'])->save($data) !== false) {
                    // echo $this->db->getLastSql();
                    $this->success('操作成功！',__MODULE__.'/agent/index');
                } else {
                    $this->error('操作失败!');
                }
            } else {
                if ($data['parentid']) {
                    // 更新 'parentid' and 'arrparentid'
                    $parentcat = $this->db->where("id = %d",$data['parentid'])->find();
                    if (!$parentcat) {
                        $this->error('上级代理不存在');
                        exit(0);
                    }
                    $data['arrparentid'] = $parentcat['arrparentid'] . "," .$parentcat['id'];
                    $data['level'] = $parentcat['level'] + 1;
                    $this->db->startTrans();
                    if ($this->db->where("id = %d",$_POST['id'])->save($data) !== false) {
                        /* 更新原上级代理 */
                        $origin_parentcat = $this->db->where("id = %d",$cat['parentid'])->find();
                        if ($origin_parentcat) {
                            $arrchildid = explode(',', $origin_parentcat['arrchildid']);
                            foreach ($arrchildid as $key => $value) {
                                if ($value == $cat['id']) {
                                    unset($arrchildid[$key]);
                                    break;
                                }
                            }
                            $arrchildid = join(',',$arrchildid);
                            $origin_parent_data = array('arrchildid' => $arrchildid);
                            if ($arrchildid == $origin_parentcat['id']) {
                                $origin_parent_data['child'] = 0;
                            }
                            if ($this->db->where("id = %d",$origin_parentcat['id'])->save($origin_parent_data) === false) {
                                $this->db->rollback();
                                $this->error("更新原上级代理失败!");
                            };
                        }
                        /* 更新现上级代理 */
                        $data_parent = array('child' => 1, 'arrchildid' => $parentcat['arrchildid'] . "," . $cat['id']);
                        if ($this->db->where("id = %d",$data['parentid'])->save($data_parent) !== false) {
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
                    if ($this->db->where("id = %d",$_POST['id'])->save($data) !== false) {
                        $origin_parentcat = $this->db->where("id = %d",$cat['parentid'])->find();
                        $arrchildid = explode(',', $origin_parentcat['arrchildid']);
                        foreach ($arrchildid as $key => $value) {
                            if ($value == $cat['id']) {
                                unset($arrchildid[$key]);
                                break;
                            }
                        }
                        $arrchildid = join(',',$arrchildid);
                        $origin_parent_data = array('arrchildid' => $arrchildid);
                        if ($arrchildid == $origin_parentcat['id']) {
                            $origin_parent_data['child'] = 0;
                        }
                        if ($this->db->where("id = %d",$origin_parentcat['id'])->save($origin_parent_data) !== false) {
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
            $id = (isset($_GET['id']) ? intval($_GET['id']) : 0);
            $agent = D("User")->where('level != 0')->order('arrparentid asc, listorder desc, id asc')->select();
            $cats = list_to_tree($agent,'id','parentid');
            $agents=$this->findChild($cats);
            $agent = $this->db->where("id = %d",$id)->find();
            $this->assign('agent',$agent);
            $this->assign('id',$id);
            $this->assign('agents',$agents);
            $this->display();
        }
    }

    public function del() {
        if ($this->db->where("parentid = %d",$_GET['id'])->count()) {
            $this->error('请先删除子代理信息！');
        }
        if ($this->db->where("id = %d", $_GET['id'])->delete() !== false) {
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