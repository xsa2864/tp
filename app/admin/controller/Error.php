<?php
namespace app\admin\controller;

use think\facade\View;
use think\Request;

class Error
{
    public function index()
    {
        return View::fetch("base/error");
    }    
}