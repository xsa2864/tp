<?php
namespace app\admin\controller;

use think\facade\DB;
use think\facade\View;
use think\facade\Session;

class Item extends Base
{
	// 产品列表
	public function itemList()
	{	
		$cate_id 	= input("cate_id",0);
		$title 		= input("title",'');
		$stime 		= input("stime",'');
		$etime 		= input("etime",'');
		$page		= input("page",1);
		$pagesize	= input("pagesize",10);

		$where 		= array();
		if($cate_id>0){
			$where[] = ['i.cate_id','=',$cate_id];
		}
		if($title){
			$where[] = ['i.title','like',"%$title%"];
		}
		if($stime){
			$where[] = ['i.add_time','>=',strtotime($stime)];
		}
		if($etime){
			$where[] = ['i.add_time','<',strtotime($etime)];
		}
		//数据列表
		$list = DB::name("item")->alias('i')
				->field("i.*,c.cate_name")
				->leftJoin("item_cate c","c.id=i.cate_id")
				->where($where)
				->order("i.id",'desc')
				->page($page,$pagesize)
				->select();
		View::assign("list",$list);
		// 统计数量
		$count = DB::name("item")->alias('i')
				->field("i.*,c.cate_name")
				->leftJoin("item_cate c","c.id=i.cate_id")
				->where($where)
				->count();
		View::assign("count",$count);
		//分类
		$cate = DB::name("item_cate")->select();
		View::assign("cate",$cate);
		
		View::assign("cate_id",$cate_id);
		View::assign("tit",$title);
		View::assign("stime",$stime);
		View::assign("etime",$etime);
		View::assign("page",$page);
		View::assign("title","产品列表");
		return View::fetch("itemList");
	}

	// 添加产品
	public function itemAdd()
	{
		$id = input("id",0);
		$list = DB::name("item")->where("id",$id)->find();
		View::assign("list",$list);

		if($id){
			View::assign("title","添加产品");
		}else{
			View::assign("title","编辑产品列表");
		}
		$cate = DB::name("item_cate")->select();
		View::assign("cate",$cate);
		return View::fetch("itemAdd");
	}
	// 保存产品
    public function itemSave(){
    	$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['title']     		= input("title","");
    	$data['promotion']  	= input("promotion","");
    	$data['cate_id']   		= input("cate_id",0);
        $data['thumb_pic']  	= input("thumb_pic","");
        $data['pics']   		= input("pics","");
        $data['content']   		= input("content","");
        $data['weight']    		= input("weight",0);
    	$data['original_price'] = input("original_price",0);
    	$data['price']    		= input("price",0);
    	$data['limit_num']    	= input("limit_num",0);
    	$data['store']   		= input("store",0);
        $data['sales']   		= input("sales",0);
    	$data['status']    		= input("status",0);
    	$data['postage_free']   = input("postage_free",0);
    	if(empty($data['title'])){
    		$re_msg['msg'] = '商品标题不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($id>0){
    		$rs = DB::name("item")->where("id",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$data['add_time'] = time();
    		$rs = DB::name("item")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
    }
	// 更改产品状态
	public function itemChangeStatus(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '更新失败';
		$id = input("id",0);
		$status = DB::name("item")->where("id",$id)->value("status");
		$data['status'] = $status==1?0:1;
		$rs = DB::name("item")->where("id",$id)->update($data);
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= $status==1?'已下架':'已发布';
		}
		echo json_encode($re_msg);
	}
	//删除产品
	public function itemDel(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '删除失败';
		$id = input("id",0);

		$n = 0;
		$arr_id = explode(',', $id);
		foreach ($arr_id as $key => $value) {
			$rs = DB::name("item")->where("id",$value)->delete();
			if($rs){
				$n++;
			}
		}
		
		if($n==count($arr_id)){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= '删除成功';
		}else if($n<count($arr_id) && $n>0){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= '部分删除成功';
		}
		echo json_encode($re_msg);
	}	

	// 产品分类
	public function itemCategory(){
		$array = DB::name("item_cate")->select();
		$list = $this->getRule($array);
		View::assign("list",$list);
		View::assign("title","产品分类");
		return View::fetch("itemCategory");
	}
	// 删除分类
	public function categoryDel()
	{
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '删除失败';
		$id = input("id",0);
		$result = DB::name("item_cate")->where("id",$id)->find();
		if($result){
			if($result['pid']==0){
				$rs = DB::name("item_cate")->where("id",$id)->delete();
				DB::name("item_cate")->where("pid",$id)->delete();
			}else{
				$rs = DB::name("item_cate")->where("id",$id)->delete();
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
                $value['child'] = $this->getc_Rule($arr,$value['id'],$check);
                $data[] = $value;
            }
        }
        return $data;
    }
	// 产品分类管理
	public function categoryAdd(){
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑分类");
		}else{
			View::assign("title","添加分类");
		}		
		$item = DB::name("item_cate")->where("id",$id)->find();
		View::assign("item",$item);
		$list = DB::name("item_cate")->where("pid",0)->select();
		View::assign("list",$list);
		return View::fetch("categoryAdd");
	}
	// 保存分类
	public function categorySave()
	{
		$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['pid']     		= input("pid",0);
    	$data['cate_name']     	= input("cate_name","");
    	if(empty($data['cate_name'])){
    		$re_msg['msg'] = '分类名称不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($id>0){
    		$rs = DB::name("item_cate")->where("id",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$rs = DB::name("item_cate")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
	}
}