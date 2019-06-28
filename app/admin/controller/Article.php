<?php
namespace app\admin\controller;

use think\facade\DB;
use think\facade\View;
use think\facade\Session;
use think\facade\Env;
use app\root\model\FileupLoad;

class Article extends Base
{
	// 文章列表
	public function articleList()
	{	
		$cate_id 	= input("cate_id",0);
		$title 		= input("title",'');
		$stime 		= input("stime",'');
		$etime 		= input("etime",'');
		$page		= input("page",1);
		$pagesize	= input("pagesize",10);

		$where 		= array();
		if($cate_id>0){
			$where[] = ['a.cate_id','=',$cate_id];
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
		$count = DB::name("article")->alias("a")
				->leftJoin("article_cate c","c.id=a.cate_id")
				->where($where)
				->count();
		View::assign("count",$count);
		// 数据列表	
		$list = DB::name("article")->alias("a")
				->field("a.*,c.name")
				->leftJoin("article_cate c","c.id=a.cate_id")
				->where($where)
				->order("a.id",'desc')
				->page($page,$pagesize)
				->select();
		View::assign("list",$list);
		//分类
		$cate = DB::name("article_cate")->select();
		View::assign("cate",$cate);

		View::assign("cate_id",$cate_id);
		View::assign("tit",$title);
		View::assign("stime",$stime);
		View::assign("etime",$etime);
		View::assign("page",$page);
		View::assign("title"," 资讯列表");
		return View::fetch("articlelist");
	}

	// 更改文章状态
	public function articleChangeStatus(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '更新失败';
		$id = input("id",0);
		$status = DB::name("article")->where("id",$id)->value("status");
		$data['status'] = $status==1?0:1;
		$rs = DB::name("article")->where("id",$id)->update($data);
		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= $status==1?'已下架':'已发布';
		}
		echo json_encode($re_msg);
	}

	// 添加文章
	public function articleAdd(){
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑咨询");
		}else{
			View::assign("title","添加咨询");
		}		
		$list = DB::name("article")->where("id",$id)->find();
		View::assign("list",$list);
		$cate = DB::name("article_cate")->select();
		View::assign("cate",$cate);
		return View::fetch("articleAdd");
	}

	// 保存文章
    public function articleSave(){
    	$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['title']     = input("title","");
    	$data['subtitle']  = input("subtitle","");
    	$data['cate_id']   = input("cate_id",0);
        $data['head_img']  = input("head_img","");
    	$data['content']   = input("content","");
    	$data['status']    = input("status",0);
    	if(empty($data['title'])){
    		$re_msg['msg'] = '标题不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($id>0){
    		$rs = DB::name("article")->where("id",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$data['addtime'] = time();
    		$rs = DB::name("article")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 2;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
    }

	//删除文章
	public function articleDel(){
		$re_msg['success'] 	= 0;
		$re_msg['msg']		= '删除失败';
		$id = input("id",0);
		$n = 0;
		$arr_id = explode(',', $id);
		$rs = DB::name("article")->delete($arr_id);

		if($rs){
			$re_msg['success'] 	= 1;
			$re_msg['msg']		= '删除成功';
		}
		echo json_encode($re_msg);
	}	

	// 资讯分类
	public function articleCategory(){
		$array = DB::name("article_cate")->select();
		$list = $this->getRule($array);
		View::assign("list",$list);
		View::assign("title","资讯分类");
		return View::fetch("articleCategory");
	}
	// 删除资讯
	public function categoryDel()
	{
		$re_msg['success'] 	= 0;
		$re_msg['msg'] 		= '删除失败';
		$id = input("id",0);
		$result = DB::name("article_cate")->where("id",$id)->find();
		if($result){
			if($result['pid']==0){
				$rs = DB::name("article_cate")->where("id",$id)->delete();
			 DB::name("article_cate")->where("pid",$id)->delete();
			}else{
				$rs = DB::name("article_cate")->where("id",$id)->delete();
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
	// 资讯分类管理
	public function categoryAdd(){
		$id = input("id",0);
		$list = array();
		if($id){
			View::assign("title","编辑分类");
		}else{
			View::assign("title","添加分类");
		}		
		$item = DB::name("article_cate")->where("id",$id)->find();
		View::assign("item",$item);
		$list = DB::name("article_cate")->where("pid",0)->select();
		View::assign("list",$list);
		return View::fetch("categoryAdd");
	}
	// 保存资讯分类
	public function categorySave()
	{
		$re_msg['success'] = 0;
        $re_msg['msg'] = '添加失败';

    	$id = input("id",0);
    	$data['pid']     	= input("pid",0);
    	$data['name']     	= input("name","");
    	if(empty($data['name'])){
    		$re_msg['msg'] = '分类名称不能为空';
    		echo json_encode($re_msg);exit;
    	}
    	if($id>0){
    		$rs = DB::name("article_cate")->where("id",$id)->update($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '更新成功';
    		}else{
    			$re_msg['msg'] = '更新失败';
    		}
    	}else{
    		$rs = DB::name("article_cate")->insertGetId($data);
    		if($rs){
    			$re_msg['success'] = 1;
        		$re_msg['msg'] = '添加成功';
    		}
    	}
    	echo json_encode($re_msg);
	}
}