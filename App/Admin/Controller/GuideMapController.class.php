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
 * 视力管理
 */
class GuideMapController extends CommonController {

    private $db;
    function __construct() {
        parent::__construct();
        $this->db = D("GuideMap");
        ;
    }

    public function index() {
        $guidemaps = $this->db->order('orderlist desc')->select();
        $this->assign('guidemaps', $guidemaps);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            if(empty($_POST['info']['pic'])){
            $info=$this->upload();
            $_POST['info']['pic'] =$_SERVER ['HTTP_HOST'].__ROOT__."/Uploads/".$info['photo']['savepath'].$info['photo']['savename'];
            }

            $this->checkToken();
            if ($this->db->add($_POST['info']) > 0) {
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("guidemaps", $this->db->select());
            $this->display();
        }
    }

    public function edit() {
        $id = $_GET['id'];
        if (IS_POST) {
            $this->checkToken();
            //$_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if ($this->db->where(array('id' => $_POST['id']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/GuideMap/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/GuideMap/index');
            }
        } else {
            if (empty($id)) {
                $this->error('异常操作！', __MODULE__ . '/GuideMap/index');
            }

            $GuideMap = $this->db->find($id);
            $this->assign('id', $id);
            $this->assign("GuideMap", $GuideMap);
            $this->display();
        }
    }

    public function del() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('异常操作！', __MODULE__ . '/GuideMap/index');
        }
        $result = $this->db->where("id = %d", $id)->delete();
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/GuideMap/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/GuideMap/index');
        }
    }

    public function listorder() {
        if (isset($_POST['sort']) && is_array($_POST['sort'])) {
            $sort = $_POST['sort'];
            foreach ($sort as $k => $v) {
                $this->db->where(array('id'=>$k))->save(array('orderlist'=>$v));
            }
        }
        $this->success('排序成功');
    }

    public function upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();

        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功
            //$this->success('上传成功！');
            return $info;
        }
    }


}
