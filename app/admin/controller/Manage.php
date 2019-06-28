<?php
namespace app\admin\controller;

use think\facade\DB;
use think\facade\View;
use think\facade\Session;

class Manage extends Base
{
	// 管理员列表
	public function manageList()
	{	
		$count = DB::name("manage")->count();
		$list = DB::name("manage")->alias("m")
				->field("m.*,a.title")
				->leftJoin("auth_group_access g","g.uid=m.mid")
				->leftJoin("auth_group a","a.id=g.group_id")
				->select();
		View::assign("count",$count);
		View::assign("list",$list);
		return View::fetch("managelist");
	}

	// 添加管理员
	public function manageAdd()
	{
		$mid = input("mid",0);
		$list = array();
		if($mid){
			View::assign("title","编辑管理员");
		}else{
			View::assign("title","添加管理员");
		}		
		$list = DB::name("manage")->where("mid",$mid)->find();
		View::assign("list",$list);
		$roler = DB::name("auth_group")->select();
		View::assign("roler",$roler);
		$gid = DB::name("auth_group_access")->where("uid",$mid)->value("group_id");
		View::assign("gid",$gid);
		return View::fetch("manageadd");
	}
	// 修改密码
    public function changePwd()
    {
    	$mid = input("mid",0);
    	$user = DB::name("manage")->where("mid",$mid)->find();
    	View::assign("user",$user);
    	View::assign("title","修改密码");
		return View::fetch("changePwd");
    }
	public function passwordSave()
    {
    	$re_msg['success'] = 0;
        $re_msg['msg'] = '修改失败';

    	$mid = input("mid",0);
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
    	if($mid>0){
    		$data['password'] = md5($password);
    		$rs = DB::name("manage")->where("mid",$mid)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '修改成功';
    		}
    	}
    	echo json_encode($re_msg);
    }
	// 删除管理员
	public function manageDel()
	{
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '删除失败';
		$mid = input("mid",0);
		if($mid==1){
			$re_msg['msg'] 		= '超级管理员不能删除';
			echo json_encode($re_msg);
			exit;
		}
		$rs = DB::name("manage")->where("mid",$mid)->delete();
		if($rs){
			DB::name("auth_group_access")->where("uid",$mid)->delete();
			$re_msg['success'] 	= 1;
			$re_msg['msg'] 		= '删除成功';
		}
		echo json_encode($re_msg);
	}

	// 保存管理员资料
	public function manageSave(){
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '执行失败';
		$mid = input("mid",0);
		$data['user_name'] 	= input("user_name");
	    $password		 	= input("password","123456");
	    $password2		 	= input("password2","123456");
	    $data['mobile'] 	= input("mobile");
	    $data['status'] 	= input("status",1);
	    $data['email'] 		= input("email");
	    $data['password'] 	= md5($password);
	    $group_id		 	= input("group_id",0);
	    if($password!=$password2){
	    	$re_msg['msg'] = '两次输入的密码不一致';
	    	echo json_encode($re_msg);exit;
	    }
	    if($mid>0){	    	
	    	$rs = DB::name("manage")->where("mid",$mid)->update($data);
	    	if($rs){
	    		$re_msg['success'] = 1;
				$re_msg['msg'] = '更新成功';
	    	}
	    }else{
	    	$result = DB::name("manage")->where("user_name",$data['user_name'])->find();
	    	if($result){
	    		$re_msg['msg'] = '用户名已存在';
	    	}else{	    		
		    	$data['add_time'] 	= time();
		    	$mid = $rs = DB::name("manage")->insertGetId($data);
		    	if($rs){
		    		$re_msg['success'] = 1;
					$re_msg['msg'] = '添加成功';
		    	}
	    	}
	    }
	    if($group_id){
	    	DB::name("auth_group_access")->where("uid",$mid)->delete();
	    	$gs = DB::name("auth_group_access")->insert(array("uid"=>$mid,"group_id"=>$group_id));
	    	if($gs || $rs){
	    		$re_msg['success'] = 1;
				$re_msg['msg'] = $rs>1?'添加成功':'更新成功';
	    	}
	    }
	    echo json_encode($re_msg);
	}

	// 角色管理
	public function roleList()
	{
		$count = DB::name("auth_group")->count();
		$list = DB::name("auth_group")->select();
		View::assign("count",$count);
		View::assign("list",$list);
		View::assign("title","角色列表");
		return View::fetch("roleList");
	}

	// 添加角色
	public function roleAdd()
	{
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑角色");
		}else{
			View::assign("title","添加角色");
		}		
		$arr = array();
		$roler = DB::name("auth_group")->where("id",$id)->find();
        if(!empty($roler)){
            $arr = explode(',', $roler['rules']);
        }
		$list = DB::name("auth_rule")->select();
		$listmenu = View::getc_Rule($list,0,$arr);

		View::assign("roler",$roler);
		View::assign("listmenu",$listmenu);
		return View::fetch("roleAdd");
	}
	// 保存角色资料
	public function roleSave(){
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '执行失败';
		$id 				= input("id",0);
		$data['title'] 		= input("title");
	    $data['status'] 	= input("status",1);
	    $data['note'] 		= input("note");
	    $rules		 		= input("rules/a",array());
	    $data['rules']		= '';
	    if($rules){
	    	$data['rules']		= implode(',', $rules);
	    }	    

	    if(empty($data['title'])){
	    	$re_msg['msg'] = '角色名称不能为空';
	    	echo json_encode($re_msg);exit;
	    }
	    if($id>0){	    	
	    	$rs = DB::name("auth_group")->where("id",$id)->update($data);
	    	if($rs){
	    		$re_msg['success'] = 1;
				$re_msg['msg'] = '更新成功';
	    	}
	    }else{
	    	$rs = DB::name("auth_group")->insert($data);
	    	if($rs){
	    		$re_msg['success'] = 1;
				$re_msg['msg'] = '添加成功';
	    	}
	    }
	    echo json_encode($re_msg);
	}

	// 删除角色
	public function roleDel()
	{
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '删除失败';
		$id = input("id",0);
		
		$rs = DB::name("auth_group")->where("id",$id)->delete();
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg'] 		= '删除成功';
		}
		echo json_encode($re_msg);
	}
	// 更改账户状态
	public function changeStatus(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '更新失败';
		$id 	= input("id",0);
		$status = input("status",0);
		// $status = DB::name("manage")->where("mid",$id)->value("status");
		$data['status'] = $status;
		$rs = DB::name("manage")->where("mid",$id)->update($data);
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= $status==1?'已拉黑':'已启用';
		}
		echo json_encode($re_msg);
	}

	// 权限节点
	public function permission(){
		$array = DB::name("auth_rule")->select();
		$list = $this->getRule($array);
		View::assign("list",$list);
		View::assign("title","权限节点");
		return View::fetch("permission");
	}
	// 规则递归
    public function getRule($arr,$pid=0,$check=array()){
        $data = array();
        foreach ($arr as $key => $value) {
            if($value['pid']==$pid){
                $value['checked'] = in_array($value['id'], $check)?1:0;
                $value['child'] =$this->getc_Rule($arr,$value['id'],$check);
                $data[] = $value;
            }
        }
        return $data;
    }

	// 添加权限节点
	public function permissionAdd(){
		$id = input("id",0);
		if($id){
			View::assign("title","编辑权限节点");
		}else{
			View::assign("title","添加权限节点");
		}
		$array = DB::name("auth_rule")->select();
		$list = View::getRule($array);
		View::assign("list",$list);
		
		$item = DB::name("auth_rule")->where("id",$id)->find();
		View::assign("item",$item);
		return View::fetch("permissionAdd");
	}

	// 更新权限节点
	public function permissionEdit()
	{
		$id = input("id",0);
		$data["pid"]  		= input("pid",0);
    	$data["title"] 		= input("title","");
    	$data["name"] 		= input("name","");
    	$data["status"]  	= input("status",0);
    	$data["ismenu"]  	= input("ismenu",0);
    	$data["condition"] 	= input("condition","");

    	if($id>0){	    	
	    	$rs = DB::name("auth_rule")->where("id",$id)->update($data);
	    	if($rs){
	    		$re_msg['success'] = 1;
				$re_msg['msg'] = '更新成功';
	    	}
	    }else{
	    	$rs = DB::name("auth_rule")->insert($data);
	    	if($rs){
	    		$re_msg['success'] = 1;
				$re_msg['msg'] = '添加成功';
	    	}
	    }
	    echo json_encode($re_msg);
	}

	public function permissionDel()
	{
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '删除失败';
		$id = input("id",0);		
		$rs = DB::name("auth_rule")->where("id",$id)->delete();
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg'] 		= '删除成功';
		}
		echo json_encode($re_msg);
	}
}