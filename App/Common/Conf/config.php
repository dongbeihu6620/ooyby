<?php
$MODULE_ALLOW_LIST = array('Admin','Home','Api');
if (IS_CLI) {
    $MODULE_ALLOW_LIST[] = 'Shell';
}
return array(
	'TOKEN_ON'          => true, //是否开启令牌验证
	'TOKEN_NAME'        => '__hash__', //令牌验证的表单隐藏字段名称
	'TOKEN_TYPE'        => 'md5', //令牌哈希验证规则 默认为MD5
	'TOKEN_RESET'       => true, //令牌验证出错后是否重置令牌 默认为true
	'SESSION_EXPIRE'    => 3600,
	'URL_MODEL'         => 2,   //URL模式
	'MODULE_ALLOW_LIST'     => $MODULE_ALLOW_LIST,
	'DEFAULT_MODULE'        =>  'admin',
    'TMPL_PARSE_STRING'         => array(
        '__PUBLIC__' => BASE_URL . 'assets',
        'IMG_PATH' => BASE_URL . "assets/images",
        'CSS_PATH' => BASE_URL . "assets/css",
        'JS_PATH' => BASE_URL . "assets/js"
    )
);