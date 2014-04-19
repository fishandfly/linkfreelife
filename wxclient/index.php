<?php
/**
  * wechat php test
  */

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
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $fromType = $postObj->MsgType;
                $picUrl = $postObj->PicUrl;
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				if(!empty( $keyword ))
                {
                	$msgType = "text";
                	$contentStr = "欢迎Hello，您好You90，欢迎光临!!";
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType,$contentStr);
                	echo $resultStr;
                }else if($fromType=="image"){
                	$msgType = "text";
                	$dateNow = strtotime(date('Y-m-d H:i:s'));
                	$fileName = $fromUsername.date('YmdHis').".jpg";
                	$f = new SaeFetchurl();
                	$res = $f -> fetch($picUrl);
                	if($f->errno()==0){
						$s = new SaeStorage();
						$s->write('weixincoures',$fileName,$res);
                	//$image = file_get_contents($picUrl);
                	//file_put_contents($fromUsername.$dateNow.".jpg", $image);
                		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "success~");
                	}
                	echo $resultStr;
                }else{
                	echo "Input something...";
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
}

?>