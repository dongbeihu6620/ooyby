<?php
namespace Api\Controller;
use Think\Controller;

class VisionController extends Controller
{
    public function index()
    {
        $count = D("Vision")->count();// 查询满足要求的总记录数
        $pagecount = 20;
        $page = new \Think\Page($count, $pagecount);
        $page->parameter = $row; //此处的row是数组，为了传递查询条件
        $page->setConfig('first', '首页');
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $page->setConfig('last', '尾页');
        $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
        $show = $page->show();
        $visions = D("Vision")->visionslist()->limit($page->firstRow . ',' . $page->listRows)->select();
        //$this->assign('page',$show);
        //$this->assign('visions', $visions);
        print json_encode($visions);//$this->display('vision:index');
    }

    //添加视力数据
    public function add($uid,$sid,$eid,$left_eye,$right_eye,$add_type,$status) {
        //$arr = json_decode($vision,true);
        if (empty($sid))
        {
            $this->error('sid为空！');
        }
        if (empty($left_eye))
        {
            $this->error('左眼视力为空！');
        }
        if (empty($right_eye))
        {
            $this->error('右眼视力为空！');
        }
        $_POST['info']['uid']=$uid;
        $_POST['info']['sid']=$sid;
        $_POST['info']['eid']=$eid;
        $_POST['info']['left_eye']=$left_eye;
        $_POST['info']['right_eye']=$right_eye;
        $_POST['info']['add_time']=strtotime(date('y-m-d h:i:s',time()));
        $_POST['info']['add_type']=$add_type;
        $_POST['info']['status']=$status;

        $_POST['infos']['left_eye']=$left_eye;
        $_POST['infos']['right_eye']=$right_eye;
        if (D("Vision")->add($_POST['info']) > 0) {
            D("student")->where(array('sid' => $sid))->save($_POST['infos']);
                $this->returnApiSuccess('','');
            }
        else{
                $this->returnApiError('添加失败');
            }
    }

    //获取某条视力数据
    public function getvision($vid) {
        if(empty($vid)){
            $this->returnApiError('vid为空');
        }
        else{
            $vision = D("Vision")->find($vid);
            if ($vision){
                $this->returnApiSuccess('',$vision);
            }
            else{
                $this->returnApiError('无数据');
            }
        }
    }

    //获取某学校学生视力数据
    public function schoolvision($school) {
        if(empty($school)){
            $this->returnApiError('学校信息为空');
        }
        $visions = D("Vision")->schoolvision($school)->select();
        if ($visions){
            $this->returnApiSuccess('',$visions);
        }
         else{
             $this->returnApiError('无数据');
        }

    }

    //获取某学校某年级学生视力数据
    public function gradevision($school,$grade) {
        if(empty($school)){
            $this->returnApiError('学校信息为空');
        }
        if(empty($grade)){
            $this->returnApiError('学校信息为空');
        }
        $visions = D("Vision")->gradevision($school,$grade)->select();
        if ($visions){
            $this->returnApiSuccess('',$visions);
        }
        else{
            $this->returnApiError('无数据');
        }
    }

    //获取某班级学生视力数据
    public function classesvision($school,$grade,$classes) {
        if(empty($school)){
            $this->returnApiError('学校信息为空');
        }
        if(empty($grade)){
            $this->returnApiError('学校信息为空');
        }
        if(empty($classes)){
            $this->returnApiError('班级信息为空');
        }
        $visions = D("Vision")->classesvision($school,$grade,$classes)->select();
        if ($visions){
            $this->returnApiSuccess('',$visions);
        }
        else{
            $this->returnApiError('无数据');
        }

    }

    //获取某位学生视力信息
    public function studentvision($sid) {
        if(empty($sid)){
            $this->returnApiError('学生号为空');
        }
        $visions = D("Vision")->studentvision($sid)->select();
        if ($visions){
            $this->returnApiSuccess('',$visions);
        }
        else{
            $this->returnApiError('无数据');
        }
    }

    //获取某位学生信息
    public function getstudent($sid) {
        $visions = D("Student")->find($sid);

        print json_encode($visions);
    }

    //获取某学校学生信息
    public function school_student($school) {
        if(empty($school)){
            $this->returnApiError('学校信息为空');
        }
        $visions = D("Student")->where('school = "%s"',$school)->select();
        if ($visions){
            $this->returnApiSuccess('',$visions);
        }
        else{
            $this->returnApiError('无数据');
        }
    }

    /**
     * @param null $msg  返回正确的提示信息
     * @param flag success CURD 操作成功
     * @param array $data 具体返回信息
     * Function descript: 返回带参数，标志信息，提示信息的json 数组
     *
     */
    function returnApiSuccess($msg = null,$data = array()){
        $result = array(
            'flag' => 'Success',
            'msg' => $msg,
            'data' =>$data
        );
        print json_encode($result);
        exit();
    }

    /**
     * @param null $msg  返回具体错误的提示信息
     * @param flag success CURD 操作失败
     * Function descript:返回标志信息 ‘Error'，和提示信息的json 数组
     */
    function returnApiError($msg = null){
        $result = array(
            'flag' => 'Error',
            'msg' => $msg,
        );
        print json_encode($result);
        exit();
    }

    function convert($params, $result = null){
        switch(gettype($params)){
            case	'array':
                $tmp = array();
                foreach($params as $key => $value) {
                    if(($value = FastJSON::encode($value)) !== '')
                        array_push($tmp, FastJSON::encode(strval($key)).':'.$value);
                };
                $result = '{'.implode(',', $tmp).'}';
                break;
            case	'boolean':
                $result = $params ? 'true' : 'false';
                break;
            case	'double':
            case	'float':
            case	'integer':
                $result = $result !== null ? strftime('%Y-%m-%dT%H:%M:%S', $params) : strval($params);
                break;
            case	'NULL':
                $result = 'null';
                break;
            case	'string':
                $i = create_function('&$e, $p, $l', 'return intval(substr($e, $p, $l));');
                if(preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $params))
                    $result = mktime($i($params, 11, 2), $i($params, 14, 2), $i($params, 17, 2), $i($params, 5, 2), $i($params, 9, 2), $i($params, 0, 4));
                break;
            case	'object':
                $tmp = array();
                if(is_object($result)) {
                    foreach($params as $key => $value)
                        $result->$key = $value;
                } else {
                    $result = get_object_vars($params);
                    foreach($result as $key => $value) {
                        if(($value = FastJSON::encode($value)) !== '')
                            array_push($tmp, FastJSON::encode($key).':'.$value);
                    };
                    $result = '{'.implode(',', $tmp).'}';
                }
                break;
        }
        return $result;
    }

    /**
     * public method
     *
     *	FastJSON::decode(params:String[, useStdClass:Boolean]):*
     *
     * @param	String	valid JSON encoded string
     * @param	Bolean	uses stdClass instead of associative array if params contains objects (default false)
     * @return	*	converted variable or null
     *				is params is not a JSON compatible string.
     * @note	This method works in an optimist way. If JSON string is not valid
     * 		the code execution will die using exit.
     *		This is probably not so good but JSON is often used combined with
     *		XMLHttpRequest then I suppose that's better more protection than
     *		just some WARNING.
     *		With every kind of valid JSON string the old error_reporting level
     *		and the old error_handler will be restored.
     *
     * @example
     *		FastJSON::decode('{"param":"value"}'); // associative array
     *		FastJSON::decode('{"param":"value"}', true); // stdClass
     *		FastJSON::decode('["one",two,true,false,null,{},[1,2]]'); // array
     */
    function decode($encode, $stdClass = false){
        $pos = 0;
        $slen = is_string($encode) ? strlen($encode) : null;
        if($slen !== null) {
            $error = error_reporting(0);
            set_error_handler(array('FastJSON', '__exit'));
            $result = FastJSON::__decode($encode, $pos, $slen, $stdClass);
            error_reporting($error);
            restore_error_handler();
        }
        else
            $result = null;
        return $result;
    }

    /**
     * public method
     *
     *	FastJSON::encode(params:*):String
     *
     * @param	*		Array, Boolean, Float, Int, Object, String or NULL variable.
     * @return	String		JSON genric object rappresentation
     *				or empty string if param is not compatible.
     *
     * @example
     *		FastJSON::encode(array(1,"two")); // '[1,"two"]'
     *
     *		$obj = new MyClass();
     *		obj->param = "value";
     *		obj->param2 = "value2";
     *		FastJSON::encode(obj); // '{"param":"value","param2":"value2"}'
     */
    function encode($decode){
        $result = '';
        switch(gettype($decode)){
            case	'array':
                if(!count($decode) || array_keys($decode) === range(0, count($decode) - 1)) {
                    $keys = array();
                    foreach($decode as $value) {
                        if(($value = FastJSON::encode($value)) !== '')
                            array_push($keys, $value);
                    }
                    $result = '['.implode(',', $keys).']';
                }
                else
                    $result = FastJSON::convert($decode);
                break;
            case	'string':
                $replacement = FastJSON::__getStaticReplacement();
                $result = '"'.str_replace($replacement['find'], $replacement['replace'], $decode).'"';
                break;
            default:
                if(!is_callable($decode))
                    $result = FastJSON::convert($decode);
                break;
        }
        return $result;
    }

    // private methods, uncommented, sorry
    function __getStaticReplacement(){
        static $replacement = array('find'=>array(), 'replace'=>array());
        if($replacement['find'] == array()) {
            foreach(array_merge(range(0, 7), array(11), range(14, 31)) as $v) {
                $replacement['find'][] = chr($v);
                $replacement['replace'][] = "\\u00".sprintf("%02x", $v);
            }
            $replacement['find'] = array_merge(array(chr(0x5c), chr(0x2F), chr(0x22), chr(0x0d), chr(0x0c), chr(0x0a), chr(0x09), chr(0x08)), $replacement['find']);
            $replacement['replace'] = array_merge(array('\\\\', '\\/', '\\"', '\r', '\f', '\n', '\t', '\b'), $replacement['replace']);
        }
        return $replacement;
    }
    function __decode(&$encode, &$pos, &$slen, &$stdClass){
        switch($encode{$pos}) {
            case 't':
                $result = true;
                $pos += 4;
                break;
            case 'f':
                $result = false;
                $pos += 5;
                break;
            case 'n':
                $result = null;
                $pos += 4;
                break;
            case '[':
                $result = array();
                ++$pos;
                while($encode{$pos} !== ']') {
                    array_push($result, FastJSON::__decode($encode, $pos, $slen, $stdClass));
                    if($encode{$pos} === ',')
                        ++$pos;
                }
                ++$pos;
                break;
            case '{':
                $result = $stdClass ? new stdClass : array();
                ++$pos;
                while($encode{$pos} !== '}') {
                    $tmp = FastJSON::__decodeString($encode, $pos);
                    ++$pos;
                    if($stdClass)
                        $result->$tmp = FastJSON::__decode($encode, $pos, $slen, $stdClass);
                    else
                        $result[$tmp] = FastJSON::__decode($encode, $pos, $slen, $stdClass);
                    if($encode{$pos} === ',')
                        ++$pos;
                }
                ++$pos;
                break;
            case '"':
                switch($encode{++$pos}) {
                    case '"':
                        $result = "";
                        break;
                    default:
                        $result = FastJSON::__decodeString($encode, $pos);
                        break;
                }
                ++$pos;
                break;
            default:
                $tmp = '';
                preg_replace('/^(\-)?([0-9]+)(\.[0-9]+)?([eE]\+[0-9]+)?/e', '$tmp = "\\1\\2\\3\\4"', substr($encode, $pos));
                if($tmp !== '') {
                    $pos += strlen($tmp);
                    $nint = intval($tmp);
                    $nfloat = floatval($tmp);
                    $result = $nfloat == $nint ? $nint : $nfloat;
                }
                break;
        }
        return $result;
    }
    function __decodeString(&$encode, &$pos) {
        $replacement = FastJSON::__getStaticReplacement();
        $endString = FastJSON::__endString($encode, $pos, $pos);
        $result = str_replace($replacement['replace'], $replacement['find'], substr($encode, $pos, $endString));
        $pos += $endString;
        return $result;
    }
    function __endString(&$encode, $position, &$pos) {
        do {
            $position = strpos($encode, '"', $position + 1);
        }while($position !== false && FastJSON::__slashedChar($encode, $position - 1));
        if($position === false)
            trigger_error('', E_USER_WARNING);
        return $position - $pos;
    }
    function __exit($str, $a, $b) {
        exit($a.'FATAL: FastJSON decode method failure [malicious or incorrect JSON string]');
    }
    function __slashedChar(&$encode, $position) {
        $pos = 0;
        while($encode{$position--} === '\\')
            $pos++;
        return $pos % 2;
    }
}


?>