<?php
namespace app\admin\controller;

use think\facade\Env;
use app\root\model\FileupLoad;

class Common extends Controller
{
	// 上传图片
    public function upload(){
        FileupLoad::upload(request());
    }
}