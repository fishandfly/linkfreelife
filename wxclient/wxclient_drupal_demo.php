<?php
//drupal基础配置代码，必须存在
define('DRUPAL_ROOT', getcwd()."/../c");

include_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

//调用drupal中的views做查询，
$result = views_get_view_result("service001");

//通过循环将查询结果做输出，nid、用户id、微信openid
foreach($result as $record){
	echo $record->nid."---".
		$record->field_field_f01001[0]['raw']['target_id']."---".
		$record->field_field_f01002[0]['raw']['value']."<br>";
}
?>