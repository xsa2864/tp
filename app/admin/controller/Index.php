<?php
namespace app\admin\controller;

use think\facade\View;
use think\facade\Session;
use app\admin\controller\Base;

class Index extends Base
{
    public function index()
    {     
        return View::fetch('index');
    }

    // 系统信息
   	public function system()
   	{
   		return View::fetch('system');
   	}

    public function hello($name = 'ThinkPHP5')
    {
        // 指明给谁推送，为空表示向所有在线用户推送
		$to_uid = "";
		// 推送的url地址，使用自己的服务器地址
		$push_api_url = "http://0.0.0.0:2346/";
		$post_data = array(
		   "type" => "publish",
		   "content" => "这个是推送的测试数据",
		   "to" => $to_uid, 
		);
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $push_api_url );
		curl_setopt ($ch, CURLOPT_POST, 1 );
		curl_setopt ($ch, CURLOPT_HEADER, 0 );
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data );
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		var_export($return);
    }
    /**
     * 退出
     */
    public function logout()
    {
        cookie('user_info', null);
        echo json_encode(array('success'=>1,'msg'=>'退出成功'));
    }
}
