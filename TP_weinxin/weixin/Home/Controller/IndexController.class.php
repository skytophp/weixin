<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        header("Content-type:text");
        //1.获得参数
        $nonce=$_GET["nonce"];
        $token='imooc';
        $timestamp=$_GET["timestamp"];
        $echostr=$_GET["echostr"];
        $signature=$_GET["signature"];
        //2.形成数组,按字典序排序
        $array=array($token, $timestamp, $nonce);
        sort($array);
        //拼接成字符串,然后与signatrue进行校验
        $str=sha1(implode($array));
        if($str==$signature && $echostr){
            //第一次接入API,验证
            ob_clean(); //一定要写
            echo $echostr;
            exit;
        }else{
            $this->reponseMsg();
        }
    }

    //接收事件推送并回复
    public function reponseMsg(){
        //获取xml信息
        /*$postArr=$GLOBALS['HTTP_RAW_POST_DATA']; //不能用这个接收*/
        $postArr=file_get_contents("php://input");
        //2.处理消息类型,并设置回复类型和内容

        //xml转换成对象
        /*
         *  <xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[FromUser]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[event]]></MsgType>
            <Event><![CDATA[subscribe]]></Event>
            </xml>
         *
         * */
        $postObj=simplexml_load_string($postArr);
        /*$postObj->ToUserName='';
        $postObj->FromUserName='';
        $postObj->CreateTime='';
        $postObj->MsgType='';
        $postObj->Event='';*/
        //判断该数据包是否为订阅事件推送
        if(strtolower($postObj->MsgType)=='event'){
            //如果是关注subscribe事件
            if(strtolower($postObj->Event=='subscribe')){
                //回复用户消息
                $toUser=$postObj->FromUserName;
                $fromUser=$postObj->toUserName;
                $time=time(); //一定是时间戳,用于校验
                $MsgType='text';
                $content='欢迎关注我们的微信公众账号!';

                $template="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
                $info=sprintf($template,$toUser,$fromUser,$time,$MsgType,$content);
                echo $info;
            }
        }

        if(strtolower($postObj->MsgType)=='text'){
            switch($num=trim($postObj->Content)){
                case 1:
                    $content="home 1";
                    break;
                case "Imooc" || "imooc" || "IMOOC":
                    $content="Imooc是一家编程学习平台.";
                    break;
            }
                $template="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
                $toUser=$postObj->FromUserName;
                $fromUser=$postObj->toUserName;
                $time=time();
                $MsgType='text';
                echo sprintf($template,$toUser,$fromUser,$time,$MsgType,$content);
        }
    }
}