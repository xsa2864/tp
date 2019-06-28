<?php
namespace app\admin\controller;

use think\facade\Cookie;
use think\facade\Request;
use think\facade\View;
use think\facade\Env;


class Base
{    
    public function initialize()
    {
        $flag = false;
        if(Cookie::has('user_info')){
            $user_info = cookie("user_info");
            $user_arr = explode(":", $user_info);
            if($user_arr[1] > time()){
                $rs = db("manage")->where("mid",$user_arr[0])->find();
                $secret = 'think';
                $str = $user_arr[0].":".$rs['user_name'].":".$user_arr[1].":".$secret;
                $c_sign = md5($str);
                if($c_sign==$user_arr[2]){
                    $flag = true;
                }
            }else{
                cookie('user_info', null);
            }
        }
        if(!$flag){
            // return $this->redirect('root/login/index');
        }

        $rules = session("user_rules");       
        $where = array();
        $where[] = ['status','=',1];
        $where[] = ['ismenu','=',1];
        $where[] = ['id','in',explode(',', $rules)];
        
        $rs = db("auth_rule")->where($where)->order('sort desc')->select();
        $listmenu = $this->getc_Rule($rs);

        $manage = session("manage");
        // print_r($manage);

        // View::share('manage',$manage);
        // $this->assign("manage",33)
     //    View::share('listmenu',$listmenu);
     //    View::share('title',Session::get('user.title'));

     //    $this->userid = Session::get('user.UserId');
        $auth = new \org\Auth();
        $this->current_action = request()->module().'/'.request()->controller().'/'.lcfirst(request()->action());
        $result = $auth->check($this->current_action,session('manage.mid'));

        if(!$result){
            if (Request::instance()->isPost()){
                echo json_encode(array('success'=>0,'msg'=>'没有权限'));
                exit;
            }else{                
                if('root/index/index'!=$this->current_action){
                    // return $this->redirect('root/Error/index');
                }
            }
        }
    }
    // 递归
    public function getMenu($arr,$pid=0){
    	$data = array();
    	foreach ($arr as $key => $value) {
    		if($value['pid']==$pid){
    			$value['child'] = $this->getMenu($arr,$value['menu_id']);
    			$data[] = $value;
    		}
    	}
    	return $data;
    }
    // 规则递归
    public function getc_Rule($arr,$pid=0,$check=array()){
        $data = array();
        foreach ($arr as $key => $value) {
            if($value['pid']==$pid){
                $value['checked'] = in_array($value['id'], $check)?1:0;
                $value['child'] = $this->getc_Rule($arr,$value['id'],$check);
                $data[] = $value;
            }
        }
        return $data;
    }
    // 查询操作平台
    public function get_platform(){
        $platform = '4';
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($agent, 'windows nt')){
            $platform = 'windows';
        }else if(strpos($agent, 'mac os')){
            $platform = 'IOS';
        }else if(strpos($agent, 'iphone')){
            $platform = 'iphone';
        }else if(strpos($agent, 'android')){
            $platform = 'android';
        }else if(strpos($agent, 'ipad')){
            $platform = 'ipad';
        }
        return $platform;
    }

}