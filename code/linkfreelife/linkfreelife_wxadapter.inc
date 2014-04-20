<?php 
define('WXITEM_TYPE_PHOTO','1');	//微信条目的类型-图片

/**
 * 设定缺省项目或项目地点
 * 
 * @param unknown $openid：用户的微信openid
 * @return 
 */
function linkfreelife_selectproject($openid){	
	list($tr03_list) = rules_invoke_component('rules_r002',$openid);
	
	$return_list;
	foreach ($tr03_list as $tr03){
		$return_list[] = array('title'=>$tr03->title, 'nid'=>$tr03->nid);
	}
	
	return $return_list;
}

/**
 * 设定缺省项目或项目地点
 *
 * @param unknown $openid：用户的微信openid
 * @param unknown $wxtype: wxitem的类型
 * @param unknown $wxvalue: wxitem的内容，例如URL或地理信息等
 * 
 * @return array 保存后的可用链接（查看、编辑、加备注）
 */
function linkfreelife_savewxitem($openid,$wxtype,$wxvalue){
	echo "Hello SaveWxItem";
}

/**
 * 用微信客户端进行用户登录
 */
function linkfreelife_client_user_login($username,$password){
	global $user;
	
	$uid = user_authenticate($username,$password);
	$arr = array ('name'=>$username,'pass'=>$password);
	if ($uid){
		$user = user_load($uid);
		user_login_finalize($arr);
	}
}
?>