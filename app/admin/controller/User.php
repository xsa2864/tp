<?php
namespace app\admin\controller;

use think\facade\DB;
use think\facade\View;
use think\facade\Session;
use think\facade\Env;
use app\root\model\FileupLoad;

class User extends Base
{
	// 用户列表
	public function userList()
	{	
		$search_str = input("search_str",'');
		$stime 		= input("stime",'');
		$etime 		= input("etime",'');
		$page		= input("page",1);
		$pagesize	= input("pagesize",20);

		$where 		= array();		
		if($search_str){
			$where[] = ['a.user_name','like',"%$search_str%"];
		}
		if($stime){
			$where[] = ['a.add_time','>=',strtotime($stime)];
		}
		if($etime){
			$where[] = ['a.add_time','<',strtotime($etime)];
		}
		// 统计数量
		$count = DB::name("user")->alias("a")
				->where($where)
				->count();
		View::assign("count",$count);
		// 数据列表	
		$list = DB::name("user")->alias("a")
				->where($where)
				->order("a.uid",'desc')
				->page($page,$pagesize)
				->select();
		View::assign("list",$list);

		View::assign("search_str",$search_str);
		View::assign("stime",$stime);
		View::assign("etime",$etime);
		View::assign("page",$page);
		View::assign("title","用户列表");
		return View::fetch("userList");
	}

	// 更改用户状态
	public function userChangeStatus(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '更新失败';
		$id = input("id",0);
		$status = DB::name("user")->where("uid",$id)->value("status");
		$data['status'] = $status==1?0:1;
		$rs = DB::name("user")->where("uid",$id)->update($data);
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= $status==1?'已拉黑':'已启用';
		}
		echo json_encode($re_msg);
	}

	// 添加用户
	public function userAdd(){
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑用户");
		}else{
			View::assign("title","添加用户");
		}		
		$list = DB::name("user")->where("uid",$id)->find();
		View::assign("list",$list);
		$cate = DB::name("article_cate")->select();
		View::assign("cate",$cate);
		return View::fetch("userAdd");
	}

	// 保存用户
    public function userSave(){
    	$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['user_name'] 	= input("user_name","");
    	$data['mobile']  	= input("mobile","");
    	$data['email']   	= input("email","");
        $data['nick_name']  = input("nick_name","");
    	$data['id_card']   	= input("id_card","");
    	$data['status']    	= input("status",1);
    	$data['sex']    	= input("sex",1);
    	if(empty($data['user_name'])){
    		$re_msg['msg'] = '用户名不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($id>0){
    		$rs = DB::name("user")->where("uid",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$data['add_time'] = time();
    		$rs = DB::name("user")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 2;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
    }
    // 修改密码
    public function changePwd()
    {
    	$uid = input("uid",0);
    	$user = DB::name("user")->where("uid",$uid)->find();
    	View::assign("user",$user);
    	View::assign("title","修改密码");
		return View::fetch("changePwd");
    }
    public function passwordSave()
    {
    	$re_msg['success'] = 0;
        $re_msg['msg'] = '修改失败';

    	$uid = input("uid",0);
    	$password = input("newpassword","");
		$password2 = input("newpassword2",""); 	
    	if(empty($password)){
    		$re_msg['msg'] = '密码不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($password != $password2){
    		$re_msg['msg'] = '2次输入的密码要一致';
    		echo json_encode($re_msg);exit;
    	}
    	if($uid>0){
    		$data['password'] = md5($password);
    		$rs = DB::name("user")->where("uid",$uid)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '修改成功';
    		}
    	}
    	echo json_encode($re_msg);
    }
	//删除用户
	public function userDel(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '删除失败';
		$uid = input("uid",0);
		$n = 0;
		$arr_id = explode(',', $uid);
		$rs = DB::name("user")->delete($arr_id);
		
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= '删除成功';
		}
		echo json_encode($re_msg);
	}	
	
}