<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

// OneThink常量定义
const ONETHINK_VERSION = '1.0.131218';
const ONETHINK_ADDON_PATH = './Addons/';





/**
 * 方法增强，根据$length自动判断是否应该显示...
 * 字符串截取，支持中文和其他编码
 * QQ:125682133
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr_local($str, $start = 0, $length, $charset = "utf-8") {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	return (strlen ( $str ) > strlen ( $slice )) ? $slice . '...' : $slice;
}






/**
 * 获取顶级模型信息
 */
function get_top_model($model_id = null) {
	$map = array (
			'status' => 1,
			'extend' => 0 
	);
	if (! is_null ( $model_id )) {
		$map ['id'] = array (
				'neq',
				$model_id 
		);
	}
	$model = M ( 'Model' )->where ( $map )->field ( true )->select ();
	foreach ( $model as $value ) {
		$list [$value ['id']] = $value;
	}
	return $list;
}


function get_cover_url($cover_id) {
	$url = get_cover ( $cover_id, 'path' );
	if (empty ( $url ))
		return '';
	
	return SITE_URL . $url;
}
// 兼容旧方法
function get_picture_url($cover_id) {
	return get_cover_url ( $cover_id );
}
function get_img_html($cover_id) {
	$url = get_cover_url ( $cover_id );
	
	if (empty ( $url ))
		return '';
	
	return '<img class="list_img" src="' . $url . '" >';
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
// weiphp 该函数是从admin的function的文件里提取这到里
//function parse_config_attr($string) {
//	$array = preg_split ( '/[;\r\n]+/', trim ( $string, ",;\r\n" ) );
//	if (strpos ( $string, ':' )) {
//		$value = array ();
//		foreach ( $array as $val ) {
//			list ( $k, $v ) = explode ( ':', $val );
//			$value [$k] = $v;
//		}
//	} else {
//		$value = $array;
//	}
//	foreach ( $value as &$vo ) {
//		$vo = clean_hide_attr ( $vo );
//	}
//	// dump($value);
//	return $value;
//}
function clean_hide_attr($str) {
	$arr = explode ( '|', $str );
	return $arr [0];
}
function get_hide_attr($str) {
	$arr = explode ( '|', $str );
	return $arr [1];
}
// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string) {
	if (0 === strpos ( $string, ':' )) {
		// 采用函数定义
		return eval ( substr ( $string, 1 ) . ';' );
	}
	$array = preg_split ( '/[;\r\n]+/', trim ( $string, ",;\r\n" ) );
	// dump($array);
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val );
			empty ( $v ) && $v = $k;
			$k = clean_hide_attr ( $k );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	// dump($value);
	return $value;
}
/* 解析列表定义规则 */
function get_list_field($data, $grid, $model) {
	// 获取当前字段数据
	foreach ( $grid ['field'] as $field ) {
		$array = explode ( '|', $field );
		$array [0] = trim ( $array [0] );
		$temp = $data [$array [0]];
		// 函数支持
		if (isset ( $array [1] )) {
			if ($array [1] == 'get_name_by_status') {
				$temp = get_name_by_status ( $temp, $array [0], $model ['id'] );
			} else {
				$temp = call_user_func ( $array [1], $temp );
			}
		}
		$data2 [$array [0]] = $temp;
	}
	if (! empty ( $grid ['format'] )) {
		$value = preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use($data2) {
			return $data2 [$match [1]];
		}, $grid ['format'] );
	} else {
		$value = implode ( ' ', $data2 );
	}
	
	// 链接支持
	if (! empty ( $grid ['href'] )) {
		$links = explode ( ',', $grid ['href'] );
		foreach ( $links as $link ) {
			$array = explode ( '|', $link );
			$href = $array [0];
			if (preg_match ( '/^\[([a-z_]+)\]$/', $href, $matches )) {
				$val [] = $data2 [$matches [1]];
			} else {
				$show = isset ( $array [1] ) ? $array [1] : $value;
				// 增加跳转方式处理 weiphp
				$target = '_self';
				if (preg_match ( '/target=(\w+)/', $href, $matches )) {
					$target = $matches [1];
					$href = str_replace ( '&' . $matches [0], '', $href );
				}
				
				// 替换系统特殊字符串
				$href = str_replace ( array (
						'[DELETE]',
						'[EDIT]',
						'[MODEL]' 
				), array (
						'del?id=[id]&model=[MODEL]',
						'edit?id=[id]&model=[MODEL]',
						$model ['id'] 
				), $href );
				
				// 替换数据变量
				$href = preg_replace_callback ( '/\[([a-z_]+)\]/', function ($match) use($data) {
					return $data [$match [1]];
				}, $href );
				
				// 兼容多种写法
				if (strpos ( $href, '?' ) === false && strpos ( $href, '&' ) !== false) {
					$href = preg_replace ( "/&/i", "?", $href, 1 );
				}
				if ($show == '删除') {
					$val [] = '<a class="ajax-post"  data-confirm="确定删除?"  hide-data="true" href="' . urldecode ( U ( $href, $GLOBALS ['get_param'] ) ) . '">' . $show . '</a>';
				} else {
					$val [] = '<a  target="' . $target . '" href="' . urldecode ( U ( $href, $GLOBALS ['get_param'] ) ) . '">' . $show . '</a>';
				}
			}
		}
		$value = implode ( ' ', $val );
	}
	return $value;
}
/**
 * 获取状态值对应的标题
 *
 * @author weiphp
 */
function get_name_by_status($val, $name, $model_id) {
	static $_name = array ();
	if (! isset ( $_name [$model_id] )) {
		$_name [$model_id] = array ();
		$map ['extra'] = array (
				'EXP',
				'!=""' 
		);
		$map ['model_id'] = $model_id;
		$list = M ( 'attribute' )->where ( $map )->select ();
		foreach ( $list as $attr ) {
			if (empty ( $attr ['extra'] ))
				continue;
			
			$extra = parse_config_attr ( $attr ['extra'] );
			if (is_array ( $extra ) && ! empty ( $extra )) {
				$_name [$model_id] [$attr ['name']] ['value'] = $extra;
				$_name [$model_id] [$attr ['name']] ['type'] = $attr ['type'];
			}
		}
	}
	
	if ($_name [$model_id] [$name] ['type'] == 'checkbox') {
		$valArr = explode ( ',', $val );
		foreach ( $valArr as $v ) {
			$res [] = empty ( $_name [$model_id] [$name] ['value'] [$v] ) ? $v : $_name [$model_id] [$name] ['value'] [$v];
		}
		
		return implode ( ', ', $res );
	} else {
		return empty ( $_name [$model_id] [$name] ['value'] [$val] ) ? $val : $_name [$model_id] [$name] ['value'] [$val];
	}
}

// php获取当前访问的完整url地址
function GetCurUrl() {
	$url = 'http://';
	if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
		$url = 'https://';
	}
	if ($_SERVER ['SERVER_PORT'] != '80') {
		$url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
	} else {
		$url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
	}
	// 兼容后面的参数组装
	if (stripos ( $url, '?' ) === false) {
		$url .= '?t=' . time ();
	}
	return $url;
}

// 获取当前用户的Token
function get_token($token = NULL) {
	if ($token !== NULL) {
		session ( 'token', $token );
	} elseif (! empty ( $_REQUEST ['token'] )) {
		session ( 'token', $_REQUEST ['token'] );
	}
	$token = session ( 'token' );
	
	if (empty ( $token )) {
		return - 1;
	}
	
	return $token;
}

/**
 * 执行SQL文件
 */
function execute_sql_file($sql_path) {
    // 读取SQL文件
    $sql = wp_file_get_contents ( $sql_path );
    $sql = str_replace ( "\r", "\n", $sql );
    $sql = explode ( ";\n", $sql );

    // 替换表前缀
    $orginal = 'oc_';
    $prefix = C ( 'DB_PREFIX' );
    $sql = str_replace ( "{$orginal}", "{$prefix}", $sql );

    // 开始安装
    foreach ( $sql as $value ) {
        $value = trim ( $value );
        if (empty ( $value ))
            continue;

        $res = M ()->execute ( $value );
        // dump($res);
        // dump(M()->getLastSql());
    }
}
// 防超时的file_get_contents改造函数
function wp_file_get_contents($url) {
    $context = stream_context_create ( array (
        'http' => array (
            'timeout' => 30
        )
    ) ) ;

    return file_get_contents ( $url, 0, $context );
}