<?php
namespace app\admin\controller;

use think\facade\DB;
use think\facade\View;
use think\facade\Session;

class Config extends Base
{
	public function index()
	{
		$config = DB::name("site_config")->where("id",1)->find();
		View::assign("config",$config);
		View::assign("title","系统配置");
		return View::fetch("siteConfig");
	}

	// 保存配置信息
	public function configSave()
	{
		$re_msg['success'] = 0;
		$re_msg['msg'] = '更新失败';
		//网站参数
		$data['site_name'] 	= input("site_name","");
    	$data['key_word'] 	= input("key_word","");
    	$data['describe'] 	= input("describe","");
    	$data['icp'] 		= input("icp","");
    	// 微信参数
    	$data['wx_appsecret'] 	= input("wx_appsecret","");
    	$data['wx_appid'] 		= input("wx_appid","");
    	$data['wx_scene_id'] 	= input("wx_scene_id","");
    	$data['wx_mchid'] 		= input("wx_mchid","");
    	$data['wx_send_name'] 	= input("wx_send_name","");
    	$data['wx_wishing'] 	= input("wx_wishing","");
    	$data['wx_client_ip'] 	= input("wx_client_ip","");
    	$data['wx_act_name'] 	= input("wx_act_name","");
    	$data['wx_remark'] 		= input("wx_remark","");
    	
    	$rs = db("site_config")->where("id",1)->update($data);
		if($rs){
			$re_msg['success'] = 1;
			$re_msg['msg'] = '更新成功';
		}
		echo json_encode($re_msg);
	}
}