<?php

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

?>