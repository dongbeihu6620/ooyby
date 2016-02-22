<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]


namespace Admin\Model;
use Think\Model;

class AgentModel extends Model {
    public function agent_list() {
        $list = $this->where('level != 0')->order('arrparentid asc, listorder desc, id asc')->select();
        return $list;
    }

    static public function wxj_category($list, $cat_id=0, $format="<option %s>%s</option>") {
        $select = "";
        foreach ($list as $key => $value) {
            $empty = "";
            for ($i=1; $i < $value[level]; $i++) {
                $empty .= '&nbsp;&nbsp;';
            }
            $select .= sprintf($format, "value='" . $value['id'] . "' " . ($cat_id == $value['id'] ? 'selected' : ''), $empty.'├─'.$value['catname']);
            if ($value['_child']) {
                $select .= CategoryModel::wxj_category($value['_child'],$cat_id);
            }
        }
        return $select;
    }
}
