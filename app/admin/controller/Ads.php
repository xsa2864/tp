<?php
namespace app\admin\controller;

use think\facade\DB;
use think\facade\View;
use think\facade\Session;
use think\facade\Env;
use app\root\model\FileupLoad;

class Ads extends Base
{
	// 文章列表
	public function adsList()
	{	
		$type_id 	= input("type_id",0);
		$title 		= input("title",'');
		$stime 		= input("stime",'');
		$etime 		= input("etime",'');
		$page		= input("page",1);
		$pagesize	= input("pagesize",10);

		$where 		= array();
		if($type_id>0){
			$where[] = ['a.type_id','=',$type_id];
		}
		if($title){
			$where[] = ['a.title','like',"%$title%"];
		}
		if($stime){
			$where[] = ['a.addtime','>=',strtotime($stime)];
		}
		if($etime){
			$where[] = ['a.addtime','<',strtotime($etime)];
		}
		// 统计数量
		$count = DB::name("ads")->alias("a")
				->leftJoin("ads_type c","c.id=a.type_id")
				->where($where)
				->count();
		View::assign("count",$count);
		// 数据列表	
		$list = DB::name("ads")->alias("a")
				->field("a.*,c.title as type_title")
				->leftJoin("ads_type c","c.id=a.type_id")
				->where($where)
				->order("a.id",'desc')
				->page($page,$pagesize)
				->select();
		View::assign("list",$list);

		//分类
		$cate = DB::name("ads_type")->select();
		View::assign("cate",$cate);

		View::assign("type_id",$type_id);
		View::assign("tit",$title);
		View::assign("stime",$stime);
		View::assign("etime",$etime);
		View::assign("page",$page);
		View::assign("title"," 广告位列表");
		return View::fetch("adslist");
	}

	// 更改状态
	public function adsChangeStatus(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '更新失败';
		$id = input("id",0);
		$status = DB::name("ads")->where("id",$id)->value("status");
		$data['status'] = $status==1?0:1;
		$rs = DB::name("ads")->where("id",$id)->update($data);
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= $status==1?'已下架':'已发布';
		}
		echo json_encode($re_msg);
	}

	// 添加广告位
	public function adsAdd(){
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑广告位");
		}else{
			View::assign("title","添加广告位");
		}		

		$list = DB::name("ads")->where("id",$id)->find();
		$list_attr = empty($list['attr'])?"":json_decode($list['attr'],1);
		View::assign("list",$list);
		View::assign("list_attr",$list_attr);
		$cate = DB::name("ads_type")->select();
		View::assign("cate",$cate);
		return View::fetch("adsAdd");
	}

	// 保存广告位
    public function adsSave(){
    	$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['title']     = input("title","");
    	$data['type_id']   = input("type_id",0);
    	$data['status']    = input("status",0);
    	$arr_img 	=  input("img","");
    	$arr_name 	=  input("name","");
    	$arr_url 	=  input("url","");
    	if(empty($data['title'])){
    		$re_msg['msg'] = '标题不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if(!empty($arr_img)){
    		$attr[] = array();
    		foreach ($arr_img as $key => $value) {
    			$attr[$key]['img']		= $value;
    			$attr[$key]['name']		= $arr_name[$key];
    			$attr[$key]['url']		= $arr_url[$key];
    		}
    		$data['attr'] = json_encode($attr);
    	}

    	if($id>0){
    		$rs = DB::name("ads")->where("id",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$data['addtime'] = time();
    		$rs = DB::name("ads")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 2;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
    }

	//删除广告位
	public function adsDel(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '删除失败';
		$id = input("id",0);
		$n = 0;
		$arr_id = explode(',', $id);
		$rs = DB::name("ads")->delete($arr_id);

		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= '删除成功';
		}
		echo json_encode($re_msg);
	}	

	// 广告位分类
	public function adsCategory(){
		$array = DB::name("ads_type")->select();
		$list = $this->getRule($array);
		View::assign("list",$list);
		View::assign("title","广告位分类");
		return View::fetch("adsCategory");
	}
	// 删除广告位
	public function typeDel()
	{
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '删除失败';
		$id = input("id",0);
		$result = DB::name("ads_type")->where("id",$id)->find();
		if($result){
			if($result['pid']==0){
				$rs = DB::name("ads_type")->where("id",$id)->delete();
				db("ads_type")->where("pid",$id)->delete();
			}else{
				$rs = DB::name("ads_type")->where("id",$id)->delete();
			}			
			if($rs){
				$re_msg['success'] 	= 1;
				$re_msg['msg'] 		= '删除成功';
			}
		}else{
			$re_msg['success'] 	= 1;
			$re_msg['msg'] 		= '删除成功';			
		}
		echo json_encode($re_msg);
	}
	// 规则递归
    public function getRule($arr,$pid=0,$check=array()){
        $data = array();
        foreach ($arr as $key => $value) {
            if($value['pid']==$pid){
                $value['checked'] = in_array($value['id'], $check)?1:0;
                $value['child'] = $this->getRule($arr,$value['id'],$check);
                $data[] = $value;
            }
        }
        return $data;
    }
	// 广告位分类管理
	public function typeAdd(){
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑分类");
		}else{
			View::assign("title","添加分类");
		}		
		$item = DB::name("ads_type")->where("id",$id)->find();
		View::assign("item",$item);
		$list = DB::name("ads_type")->where("pid",0)->select();
		View::assign("list",$list);
		return View::fetch("typeAdd");
	}
	// 保存广告位分类
	public function typeSave()
	{
		$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['pid']     	= input("pid",0);
    	$data['title']     	= input("title","");
    	if(empty($data['title'])){
    		$re_msg['msg'] = '分类名称不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($id>0){
    		$rs = DB::name("ads_type")->where("id",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$rs = DB::name("ads_type")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
	}
}