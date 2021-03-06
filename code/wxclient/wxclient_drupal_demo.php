<?php
//drupal基础配置代码，必须存在
define('DRUPAL_ROOT', getcwd()."/..");

include_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);


demo_getprojectnews();

//$filename = linkfreelife_grabimage('http://www.baidu.com/img/bdlogo.gif');

//demo_linkfreelife_selectproject_singleplace();	//设定地点
//demo_linkfreelife_savewxitem();	//保存图片

function demo_getprojectnews(){
	linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
	
	$newslist= linkfreelife_getprojectnews("oQybmtztKj91iUeKClEhzL20xh0w");
	
	foreach ($newslist as $news){
		echo "Title=".$news['title'].",nid=".$news['nid']."<br>";
	}
	
	echo "finished";
}

/**
 * 用于演示将远程图片保存到本地
 */
function  demo_grabimage(){
	$filename = linkfreelife_grabimage('http://www.baidu.com/img/bdlogo.gif');
	echo $filename;
}

/**
 * 用于演示保存上传的文件
 */
function demo_linkfreelife_savewxitem(){
	linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
	
	//$tr06id= linkfreelife_savewxitem('222',1,'http://www.baidu.com/img/bdlogo.gif');	//第2个参数=1，代表保存的是图片
	
	//oQybmtztKj91iUeKClEhzL20xh0w|||http://mmbiz.qpic.cn/mmbiz/XdZLDbLdXaxpbVbibwdb2GM0bjyJrPtzib1icGLwqiaVzFUlo41PtFQeBymXv3eJY6d2rtRbEasEzico0zCGb1W8kiaA/0
	$tr06id= linkfreelife_savewxitem('oQybmtztKj91iUeKClEhzL20xh0w',1,'http://mmbiz.qpic.cn/mmbiz/XdZLDbLdXaxpbVbibwdb2GM0bjyJrPtzib1icGLwqiaVzFUlo41PtFQeBymXv3eJY6d2rtRbEasEzico0zCGb1W8kiaA/0');	//第2个参数=1，代表保存的是图片
	
	echo "finished";
}


/**
 * 用于演示调用 设定项目 的代码
 *
 * 参与1个项目地点，已直接进行设定
 */
function demo_linkfreelife_selectproject_singleplace(){
	linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');

	$placelist = linkfreelife_selectproject('222');

	if(count($placelist)==1){
		$place = $placelist[0];
		echo "项目设定完成。<br>Title=".$place['title'].",nid=".$place['nid']."<br>";
	}
	echo "finished";
}


/**
 * 用于演示调用 设定项目 的代码
 * 
 * 参与多个项目地点，需要做二次选择的
 */
function demo_linkfreelife_selectproject_multiplace(){
	linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
	
	$placelist = linkfreelife_selectproject('111');
	
	foreach ($placelist as $place){
		echo "Title=".$place['title'].",nid=".$place['nid']."<br>";
	}

	echo "finished";
}

/**
 * 用于演示如何通过代码调用drupal的views并处理返回结果
 */
function demo_views(){
	//调用drupal中的views做查询，
	$result = views_get_view_result("service001");
	
	//通过循环将查询结果做输出，nid、用户id、微信openid
	foreach($result as $record){
		echo $record->nid."---".
				$record->field_field_f01001[0]['raw']['target_id']."---".
				$record->field_field_f01002[0]['raw']['value']."<br>";
	}
	
}
?>