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
class VisionController extends CommonController {
    public function index() {
        $aid=$_SESSION['user_info']['arrchildid'];
        $map['s.aid']  = array('in',$aid);
        //echo $aid;
        if ($_SESSION['user_info']['level'] != 0) {
            $count = D("Vision")->visionslist($map)->count();// 查询满足要求的总记录数
        }
        else {
            $count = D("Vision")->visionlist()->count();
        }
        $pagecount= 20;
        $page     = new \Think\Page($count , $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first','首页');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('last','尾页');
        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();

        if ($_SESSION['user_info']['level'] != 0){//如果是代理商
            $visions = D("Vision")->visionslist($map)->limit($page->firstRow.','.$page->listRows)->select();
        }
        else{
            $visions = D("Vision")->visionlist()->limit($page->firstRow.','.$page->listRows)->select();
        }

        $this->assign('page',$show);
        $this->assign('visions', $visions);
        $this->display('vision:index');
    }

    public function add() {
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['add_time']=strtotime($_POST['info']['add_time']);

            if (D("Vision")->add($_POST['info']) > 0) {
                //D("student")->where(array('sid' => $_POST['vid']))->save($_POST['info'])
                $this->success('操作成功！', "index");
            } else {
                $this->error('操作失败！');
            }
        } else {
            $this->assign("visions", D("Vision")->visionsList());
            $this->display();
        }
    }

    public function edit() {
        $vid = $_GET['vid'];
        if (IS_POST) {
            $this->checkToken();
            $_POST['info']['add_time']=strtotime($_POST['info']['add_time']);
            if (D("Vision")->where(array('vid' => $_POST['vid']))->save($_POST['info']) !== false) {
                $this->success('操作成功！', __MODULE__ . '/Vision/index');
            } else {
                $this->error('操作失败！', __MODULE__ . '/Vision/index');
            }
        } else {
            if (empty($vid)) {
                $this->error('异常操作！', __MODULE__ . '/Vision/index');
            }

            $vision = D("Vision")->getvision($vid);

            $this->assign('vid', $vid);
            $this->assign("vision", $vision);
            $this->assign("student",D("Student")->getstudent($vision['sid']));
            //$this->assign("members", D("Member")->memberList());
            $this->display();
        }
    }

    public function del() {
        $vid = intval($_GET['vid']);
        if (empty($vid)) {
            $this->error('异常操作！', __MODULE__ . '/Vision/index');
        }
        $result = D('Vision')->delvision($vid);
        if ( $result ) {
            $this->success('操作成功！', __MODULE__ . '/Vision/index');
        } else {
            $this->error('操作失败！', __MODULE__ . '/Vision/index');
        }
    }

    public function listorder() {
        if (isset($_POST['sort']) && is_array($_POST['sort'])) {
            $sort = $_POST['sort'];
            foreach ($sort as $k => $v) {
                $this->db->where(array('id'=>$k))->save(array('sort'=>$v));
            }
        }
        $this->success('排序成功');
    }

    //折线图
    public function barline()
    {
        $sid = intval($_GET['sid']);
        $title  = $_GET['name'];
        if (empty($sid)) {
            $this->error('学生id为空', __MODULE__ . '/Vision/index');
        }
        if (empty($title)) {
            $this->error('学生姓名为空', __MODULE__ . '/Vision/index');
        }
        $time = date('y-m-d h:i:s',time());
        $data = D("Vision")->eyeslist($sid);
        $this->assign('title', $title);
        $this->assign('time', $time);
        $this->assign("data", $data);
        $this->display();
    }


    //折线图
    public function barline2()
    {
        // 引入必要的文件，格式：vendor('Jpgraph文件夹.类名')
        vendor('Jpgraph.jpgraph');   //必须的
        vendor('Jpgraph.jpgraph_bar');   //依具体情况引入
        vendor('Jpgraph.jpgraph_line');   //依具体情况引入

        // Some (random) data
        $sid = intval($_GET['sid']);
        $title  = $_GET['name'];
        $title .= "视力情况";
        $time = date('y-m-d h:i:s',time());
        $ydata = D("Vision")->lefteyelist($sid);
        $ydata2= D("Vision")->righteyelist($sid);

// Size of the overall graph
        $width=800;
        $height=400;

// Create the graph and set a scale.
// These two calls are always required
        $graph = new \Graph($width,$height);
        $graph->SetScale('intlin');
        $graph->SetShadow();

// Setup margin and titles
        $graph->SetMargin(40,20,20,40);
        $graph->title->Set($title);
        $graph->subtitle->Set($time);
        $graph->xaxis->title->Set('测试次数');
        $graph->yaxis->title->Set('视力');
        $graph->title->SetFont(FF_SIMSUN , FS_BOLD); //设置主标题字体
        $graph->yaxis->title->SetFont( FF_SIMSUN , FS_BOLD );
        $graph->xaxis->title->SetFont( FF_SIMSUN , FS_BOLD );

// Create the first data series
        $lineplot=new \LinePlot($ydata);
        $lineplot->SetWeight( 2 );   // Two pixel wide

// Add the plot to the graph
        $graph->Add($lineplot);

// Create the second data series
        $lineplot2=new \LinePlot($ydata2);
        $lineplot2->SetWeight( 2 );   // Two pixel wide

// Add the second plot to the graph
        $graph->Add($lineplot2);

        $lineplot->SetLegend("左眼视力");
        $lineplot2->SetLegend("右眼视力");
// Display the graph
        $graph->Stroke();
    }

    //折线图
    public function barline3()
    {
        // 引入必要的文件，格式：vendor('Jpgraph文件夹.类名')
        vendor('Jpgraph.jpgraph');   //必须的
        vendor('Jpgraph.jpgraph_line');   //依具体情况引入

        $datay1 = 	array(11,7,5,8,3,5,5,4,8,6,5,5,3,2,5,1,2,0);
        $datay2 = 	array( 4,5,4,5,6,5,7,4,7,4,4,3,2,4,1,2,2,1);
        $datay3 = 	array(4,5,7,10,13,15,15,22,26,26,30,34,40,43,47,55,60,62);

// Create the graph. These two calls are always required
        $graph = new \Graph(300,200);
        $graph->SetScale("textlin");
        $graph->SetShadow();
        $graph->img->SetMargin(40,30,20,40);

// Create the linear plots for each category
        $dplot[] = new \LinePLot($datay1);
        $dplot[] = new \LinePLot($datay2);
        $dplot[] = new \LinePLot($datay3);

        $dplot[0]->SetFillColor("red");
        $dplot[1]->SetFillColor("blue");
        $dplot[2]->SetFillColor("green");

// Create the accumulated graph
        $accplot = new \AccLinePlot($dplot);

// Add the plot to the graph
        $graph->Add($accplot);

        $graph->xaxis->SetTextTickInterval(2);
        $graph->title->Set("Example 17");
        $graph->xaxis->title->Set("X-title");
        $graph->yaxis->title->Set("Y-title");

        $graph->title->SetFont(FF_FONT1,FS_BOLD);
        $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
        $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
        $graph->Stroke();

    }

    //折线图
    public function barline4()
    {
        // 引入必要的文件，格式：vendor('Jpgraph文件夹.类名')
        vendor('Jpgraph.jpgraph');   //必须的
        vendor('Jpgraph.jpgraph_bar.php');   //依具体情况引入

        $data1y=array(-8,8,9,3,5,3);  //blue那条的数据
        $data2y=array(18,20,16,10,5,6); //orange那条的数据

// Create the graph. These two calls are always required
        $graph = new \Graph(800,500);  //大小 宽*高
        $graph->SetScale("textlin"); //设置刻度模式 还有intint、linlin、log、lin、textlog等其他模式

        $graph->SetShadow();
        $graph->img->SetMargin(40,30,20,40); //设置图表边距，就跟css里margin属性是一样的

// Create the bar plots
        $b1plot = new \BarPlot($data1y);  //创建新的BarPlot对象 各种不同图表就是通过调用不通对象实现的,BarPlot就是柱状的，还有LinePlot线性图,PiePlot饼状图
        $b1plot->SetFillColor("orange"); //设置图的颜色
        $b1plot->value->Show();          //展示
        $b2plot = new \BarPlot($data2y);  //一样的
        $b2plot->SetFillColor("blue");
        $b2plot->value->Show();

// Create the grouped bar plot
        $gbplot = new \AccBarPlot(array($b1plot,$b2plot)); //开始画图了

        $graph->Add($gbplot);  //在统计图上绘制曲线

        $graph->title->Set(iconv_arr("Phpwind 图表测试"));  // 设置图表标题 这里iconv_arr是我自己加的，为了支持我们伟大的中文要把你的当前编码转化为html实体
        $graph->xaxis->title->Set(iconv_arr("这个大概是月份吧")); //设置X轴标题
        $graph->yaxis->title->Set(iconv_arr("这个是Y轴")); //设置Y轴标题
        $graph->title->SetFont(FF_SIMSUN,FS_BOLD);  //设置标题字体，这里字体默认是FF_FONT1，为了中文换成FF_SIMSUN
        $graph->yaxis->title->SetFont(FF_SIMSUN,FS_BOLD); //设置X轴标题字体
        $graph->xaxis->title->SetFont(FF_SIMSUN,FS_BOLD); //设置Y轴标题字体

        $graph->Stroke();  //输出图像

    }
}
