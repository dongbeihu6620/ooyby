<?php
/**
 * Created by PhpStorm.
 * User: chenxueyang
 * Date: 16/1/12
 * Time: 下午8:08
 */
namespace Admin\Model;
use Think\Model;

class StudentModel extends Model {
    protected $tableName = 'student';

    public function studentsList() {
        $students = $this->table('xy_student s, xy_school l,xy_classes c')
            ->where('s.school = l.id and s.classes = c.id')
            ->field('s.sid as sid, s.uid as uid,s.school as school, s.grade as grade,s.classes as classes,s.status as status,s.realname as realname,
            l.name as school,c.grade as grade,c.classes as classes')
            ->order('s.sid desc');
        return $students;
    }

    public function agent_studentsList($aid) {
        $students = $this->where('aid=47')->order("sid desc")->select();
        return $students;
    }

    public function studentvision($sid) {
        $visions = $this->table('xy_vision v, xy_student s')->where('v.sid = s.sid and s.sid=%d',$sid)
            ->field('v.vid as vid, s.sid as sid,v.left_eye as left_eye, v.right_eye as right_eye,v.add_time as add_time,s.nickname as nickname,s.realname as realname,s.gender as gender,s.age as age,s.school as school,s.grade as grade,s.classes as classes,v.status as status')
            ->order('v.vid desc');

        return $visions;
    }

    public function addstudent() {
        $datas['module'] = trim($_POST['module']);
        $datas['action'] = trim($_POST['action']);
        $datas['title'] = trim($_POST['title']);
        $datas['params'] = trim($_POST['params']);
        $datas['status'] = trim($_POST['status']);
        $datas['remark'] = trim($_POST['remark']);
        $datas['sort'] = trim($_POST['sort']);
        $datas['pid'] = $_POST['pid'];
        return $this->add($datas);
    }

    public function getstudent($sid) {
        $student = $this->find($sid);
        return $student;
    }

    public function editstudent($uid) {
        $uid = $_POST['nid'];
        $datas['module'] = trim($_POST['module']);
        $datas['action'] = trim($_POST['action']);
        $datas['title'] = trim($_POST['title']);
        $datas['params'] = trim($_POST['params']);
        $datas['status'] = trim($_POST['status']);
        $datas['remark'] = trim($_POST['remark']);
        $datas['sort'] = trim($_POST['sort']);
        $datas['pid'] = $_POST['pid'];
        return $this->where("id = %d", $uid)->save($datas);
    }

    public function delstudent($sid) {
        $sid = $_GET['sid'];
        return $this->where("sid = %d", $sid)->delete();
    }

    public function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $_SESSION['loginAccount'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function importExecl($file){
        if(!file_exists($file)){
            return array("error"=>0,'message'=>'file not found!');
        }
        Vendor("Phpexcel.PHPExcel.IOFactory");
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');//Excel5 为2003版本
        try{
            $PHPReader = $objReader->load($file);
        }catch(Exception $e){}
        if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach($allWorksheets as $objWorksheet){
            $sheetname=$objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow();//how many rows
            $highestColumn = $objWorksheet->getHighestColumn();//how many columns
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname;
            $array[$i]["Cols"] = $allColumn;
            $array[$i]["Rows"] = $allRow;
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
                foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){
                $row = array();
                for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
                    $cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col.$currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();
                    if(substr($value,0,1)=='='){
                        return array("error"=>0,'message'=>'can not use the formula!');
                        exit;
                    }
                    if($cell->getDataType()==\PHPExcel_Cell_DataType::TYPE_NUMERIC){
                        $cellstyleformat=$cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat();
                        $formatcode=$cellstyleformat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                            $value=gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                        }else{
                            $value=\PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
                        }
                    }
                    if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
                        $temp = $value;
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
                        $value=$arr[$currentRow-1][$currentColumn];
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
                        $value=$temp;
                    }
                    $row[$currentColumn] = $value;
                }
                $arr[$currentRow] = $row;
            }
            $array[$i]["Content"] = $arr;
            $i++;
        }
        spl_autoload_register(array('Think\Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
        unlink($file);
        return array("error"=>1,"data"=>$array);
    }

    /*
    删除会员
    */
    public function drop_students( $uid ) {
        $result = $this->where("uid = %d", $uid)->delete();
        return $result;
    }
}