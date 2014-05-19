<?php
use wxclient;
/**
  * wechat php test
  */
//drupal基础配置代码，必须存在
define('DRUPAL_ROOT', getcwd()."/..");

include_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
 
//define your token
define("TOKEN", "weixin");

$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){  
				//获取微信端发送来的内容。     
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;//发送人OpenId
                $toUsername = $postObj->ToUserName;//接收人OpenId
                $fromType = $postObj->MsgType;//消息类型
                $time = time();
                if($fromType=="event"){
                	//获取发送的动作类型，根据发送的动作类型，确定返回消息的类型和内容
                	$fromEvent = $postObj->Event;
                	//如果是添加关注
                	if($fromEvent=="subscribe"){
                		//获取关注之后的返回消息内容
						$joinTpl = $this->GetSubscribe($fromUsername);
                		$resultStr = sprintf($joinTpl, $fromUsername, $toUsername, $time);
                		echo $resultStr;
                	}else if($fromEvent=="CLICK"){   //如果是点击事件
                		
                		//通过drupal，读取“service001”,获取人员绑定信息
                		$result = views_get_view_result("service001");
                		//校验当前用户是否已经绑定系统
                		$checkLogin = $this->SelectIsLogin($result,$fromUsername);
                		
                		//如果账号尚未绑定系统
                		if($checkLogin==""){
                			//获取返回信息是登录时的集合
                			$joinTpl = $this->GetLogin($fromUsername);
                			 
                			//获取Tpl~登录。登录之后正常就可以选择项目
                			$resultStr = sprintf($joinTpl, $fromUsername, $toUsername, $time);
                			echo $resultStr;
                		}else{
                			
	                		//获取点击事件的编码
	                		$fromEventKey = $postObj->EventKey;
	                		//如果点击事件是“连接用户”
	                		if($fromEventKey=="LoginIn"){
	                			
	                		}else if($fromEventKey=="SetPorject"){//如果是选择项目
	                			linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
	                			$placelist = linkfreelife_selectproject($fromUsername);
	                			$placeTpl = $this->GetProjectTpl($placelist,$fromUsername);
	                			$resultStr = sprintf($placeTpl, $fromUsername, $toUsername, $time);
	                			echo $resultStr;
	                		}else if($fromEventKey=="Affiche"){//如果是获取公告           			
	                			//登录后台
	                			linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
	                			//获取当前wx用户，可以获取哪些公告
	                			$newslist= linkfreelife_getprojectnews($fromUsername);
	                			//获取公告返回集合
								$AfficheTpl = $this->GetAfficheTpl($newslist);
								$resultStr = sprintf($AfficheTpl, $fromUsername, $toUsername, $time);
								echo $resultStr;
	                		}else if($fromEventKey=="ParticipateDebate"){//如果是参与讨论
	                			$textTpl = $this->GetTextResTpl("参与讨论啊");
	                			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
	                			echo $resultStr;
	                		}else if($fromEventKey=="ProjectInfo"){
	                			linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
	                			$info = linkfreelife_selectProjectInfo($fromUsername);
	                			$textTpl = $this->GetTextResTpl($info);
	                			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
	                			echo $resultStr;
	                		}
                		}
                	}
                }else{//判断，如果微信客户端是给公众账号发送消息。     
                	$keyword = trim($postObj->Content);
          			//如果是键盘输入文字
					if(!empty( $keyword ))
	                {
	                	$textTpl = $this->GetTextResTpl("欢迎关注力度生活！");
	                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
	                	echo $resultStr;
	                }else if($fromType=="image"){
	                	//通过drupal，读取“service001”,获取人员绑定信息
	                	$result = views_get_view_result("service001");
	                	//校验当前用户是否已经绑定系统
	                	$checkLogin = $this->SelectIsLogin($result,$fromUsername);
	                	
	                	//如果账号尚未绑定系统
	                	if($checkLogin==""){
	                		//获取返回信息是登录时的集合
	                		$joinTpl = $this->GetLogin($fromUsername);
	                		
	                		//获取Tpl~登录。登录之后正常就可以选择项目
		                	$resultStr = sprintf($joinTpl, $fromUsername, $toUsername, $time);
		                	echo $resultStr;
	                	}else{//如果已登录，则保存图片 
	                		$picUrl = $postObj->PicUrl;
		                	//登录后台
                			linkfreelife_client_user_login('wxclient','LinkfreeLifeWXClient123!@#');
                			
                			//调用后台保存图片方法
							$resPicId= linkfreelife_savewxitem($fromUsername."",1,$picUrl."");	//第2个参数=1，代表保存的是图片
							//返回消息给前台
							$picTpl = $this->GetSaveimg($picUrl,$resPicId);
	                		$resultStr = sprintf($picTpl, $fromUsername, $toUsername, $time);         		
		                	echo $resultStr;
	                	}
		                	
	                }else if($fromType=="video"){
	                	$msgType = "text";
	                	$contentStr = "video";//$mediaId;
	                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType,$contentStr);
	                	echo $resultStr;
	                }else{
	                	echo "Input something...";
	                }
	        	}
	        }else {
	        	echo "";
	        	exit;
	        }
    }
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	//获取用户关注时，返回的内容集合
	private function GetSubscribe($fromUsername){
		//返回内容集合（新闻格式）
    	$joinTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>3</ArticleCount>
               					<Articles>
	               					<item>
               							<Title>欢迎关注力度生活!</Title>
               							<Description>欢迎关注力度生活</Description>
               							<PicUrl><![CDATA[http://wx.linkfree-china.com/wxclient/u34.png]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/html/welcome.html]]></Url>
	               					</item>
               						<item>
               							<Title>点击进入登陆界面</Title>
               							<Description>点击进入登陆界面</Description>
               							<PicUrl><![CDATA[http://wx.linkfree-china.com/wxclient/u38.png]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/user&openid=".$fromUsername."]]></Url>
               						</item>
               						<item>
               							<Title>查看系统使用指南</Title>
               							<Description>查看系统使用指南</Description>
               							<PicUrl><![CDATA[http://wx.linkfree-china.com/wxclient/u42.png]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/html/help.html]]></Url>
               						</item>
               					</Artilces>
								<FuncFlag>0</FuncFlag>
								</xml>";  
               			return $joinTpl;
	}
	
	//获取用户关注时，返回的内容集合
	private function GetLogin($fromUsername){
	//返回内容集合（新闻格式）
    	$joinTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>1</ArticleCount>
               					<Articles>
               						<item>
               							<Title>点击进入登陆界面</Title>
               							<Description>点击进入登陆界面</Description>
               							<PicUrl><![CDATA[http://wx.linkfree-china.com/wxclient/u38.png]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/user&openid=".$fromUsername."]]></Url>
               						</item>
               					</Artilces>
								<FuncFlag>0</FuncFlag>
								</xml>";  
               			return $joinTpl;
	}
	
	//保存图片成功时，返回的内容集合
	private function GetSaveimg($aPicUrl,$aResPicId){
		//返回内容集合（新闻格式）
		$picTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>1</ArticleCount>
               					<Articles>
	               					<item>
               							<Title>图片保存成功！</Title>
               							<Description>如需讨论，请点击参与。</Description>
               							<PicUrl><![CDATA[".$aPicUrl."]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/node/".$aResPicId."]]></Url>
	               					</item>
               					</Artilces>
								<FuncFlag>0</FuncFlag>
								</xml>";
		return $picTpl;
	}
	
	private function GetSetProject(){
	
	}
	
	//获取用户是否已经绑定Linkfree，如未绑定返回“”，如已绑定返回“1”
	private function SelectIsLogin($result,$fromUsername){
		$checkLogin = "";
		//通过循环将查询结果做输出，nid、用户id、微信openid。如果发送人OpenId和绑定中的数据有相同的，说明此人已经绑定。
		foreach($result as $record){
			$openId = $record->field_field_f01002[0]['raw']['value'];
			if($fromUsername==$openId){
				$checkLogin = "1";
				break;
			}
		}
		return $checkLogin;
	}
	
	//获取项目公告返回结果集合
	private function GetAfficheTpl($newslist){
		$listCount = count($newslist);
		if($listCount<6){
			$returnTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>".$listCount."</ArticleCount>
               					<Articles>
								        ";
			$a = 0;
			foreach ($newslist as $news){
				$a++;
				$returnTpl = $returnTpl."<item>
               							<Title>".$news['title']."</Title>
               							<Description>".$news['title']."</Description>
               							<PicUrl><![CDATA[http://wx.linkfree-china.com/wxclient/images/".$a.".png]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/node/".$news['nid']."]]></Url>
	               					</item>
		               					";
			}
			$returnTpl = $returnTpl."</Artilces>
									<FuncFlag>0</FuncFlag>
									</xml>";
			return  $returnTpl;
		}else{
			$returnTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>6</ArticleCount>
               					<Articles>
								        ";
			for ($i=0;$i<5;$i++){
				$news = $newslist[$i];
				$returnTpl = $returnTpl."<item>
               							<Title>".$news['title']."</Title>
               							<Description>".$news['title']."</Description>
               							<PicUrl><![CDATA[http://wx.linkfree-china.com/wxclient/images/".($i+1).".png]]></PicUrl>
               							<Url><![CDATA[http://wx.linkfree-china.com/node/".$news['nid']."]]></Url>
	               					</item>
		               					";
			}
			
			$returnTpl = $returnTpl."<item>
               							<Title>点击此处查看更多公告</Title>
               							<Description>more...</Description>
               							<Url><![CDATA[http://wx.linkfree-china.com/service017]]></Url>
	               					</item>
               						</Artilces>
									<FuncFlag>0</FuncFlag>
									</xml>";
			return  $returnTpl;
		}
		 
	}
	
	//获取项目集合
	private function GetProjectTpl($aPlacelist,$aFromUsername){
		$msgType = "text";
		$listCount = count($aPlacelist);
		if($listCount==0){
			$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[尚未给您设定项目。]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
			return $textTpl;
		}else if($listCount==1){
			$place = $aPlacelist[0];
			$contentStr = "项目设定完成。\nTitle=".$place['title']."\nnid=".$place['nid'];
			$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[".$contentStr."]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
			return $textTpl;
		}else if($listCount>1 && $listCount<=5){
			$showCount = $listCount+1;
			$placeTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>".$showCount."</ArticleCount>
               					<Articles>
										<item>
	               							<Title><![CDATA[请选择您要匹配的项目]]></Title>
	               							<Description><![CDATA[项目列表]]></Description>
		               					</item>
								        ";
			foreach ($aPlacelist as $place){
				$placeTpl = $placeTpl."<item>
	               							<Title>".$place['title']."</Title>
	               							<Description>".$place['title']."</Description>
	               							<Url><![CDATA[http://wx.linkfree-china.com/setprojectplace/".$place['nid']."/".$aFromUsername."]]></Url>
		               					</item>
			               					";
			}
			$placeTpl = $placeTpl."</Artilces>
											<FuncFlag>0</FuncFlag>
											</xml>";
			return $placeTpl;
		}else if($listCount>5){
			$placeTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>6</ArticleCount>
               					<Articles>
										<item>
	               							<Title><![CDATA[请选择您要匹配的项目]]></Title>
	               							<Description><![CDATA[项目列表]]></Description>
		               					</item>
								        ";
			for ($i=0;$i<4;$i++){
				$place = $aPlacelist[$i];
				$placeTpl = $placeTpl."<item>
               							<Title>".$place['title']."</Title>
               							<Description>".$place['title']."</Description>
               							<Url><![CDATA[http://wx.linkfree-china.com/setprojectplace/".$place['nid']."/".$aFromUsername."]]></Url>
	               					</item>
		               					";
			}
		
			$placeTpl = $placeTpl."<item>
               							<Title><![CDATA[更多选择请点击]]></Title>
               							<Description><![CDATA[more...]]></Description>
               							<Url><![CDATA[http://wx.linkfree-china.com/user&openid=".$aFromUsername."]]></Url>
	               					</item>
									</Artilces>
									<FuncFlag>0</FuncFlag>
									</xml>";
			return $placeTpl;
		}
	}
	
	//获取返回文本内容
	private function GetTextResTpl($aText){
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[".$aText."]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";
		return $textTpl;
	}
	
	//获取新闻格式返回内容，$aList新闻内容集合，$aCount,显示数量
	private function GetNewsResTpl($aList,$aCount){
	
		if($aCount==NULL || $aCount == 0){
			$showCount = count($aList);
		}else{
			$showCount = $aCount;
		}
		
		$newsTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>".$showCount."</ArticleCount>
					<Artilces>
					";
		//循环集合，编写返回的xml内容
		foreach ($aList as $Obj){
			$ObjTpl = $ObjTpl."<item>
		               							<Title><![CDATA[".$Obj['Title']."]]></Title>
		               							<Description><![CDATA[".$Obj['Description']."]]></Description>
               									<PicUrl><![CDATA[".$Obj['PicUrl']."]]></PicUrl>
               									<Url><![CDATA[".$Obj['Url']."]]></Url>
			               					</item>
				               					";
		}
		$placeTpl = $placeTpl."</Artilces>
											<FuncFlag>0</FuncFlag>
											</xml>";
		return $newsTpl;
	}
}

?>