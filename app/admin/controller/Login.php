<?php
namespace app\admin\controller;

use think\facade\Cookie;
use think\facade\View;
use think\helper\Hash;
use think\Request;
use think\facade\DB;
use think\facade\Queue;

class Login
{
    public function initialize()
    {
        if (Cookie::has('user_info')) {
            // return $this->redirect('root/index/index');
        }
    }

    /**
     * 显示登录表单
     * @return mixed
     */
    public function index()
    {        
        $info = Cookie::get('user_info');
        if($info){
            // $this->redirect('root/index/index');
        }
        View::assign("user_name",Cookie::get('user_name'));
        return View::fetch('login');
    }

    /**
     * 执行登录逻辑
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $re_msg['success']  = 0;
        $re_msg['msg']      = '登录失败';

        $data['username'] = input("username","");
        $data['password'] = input("password","");
        $data['captcha']  = input("captcha","");
        $online           = input("online",0);

        $username = $data['username'];
        $validate = new \app\validate\Login;  
        if (!$validate->check($data)) {
            $re_msg['msg'] = $validate->getError();
            echo json_encode($re_msg);exit;
        }        

        $user = DB::name("manage")->alias("m")
                ->join("auth_group_access ga","ga.uid=m.mid")
                ->join("auth_group g","g.id=ga.group_id")
                ->where('m.user_name',$username)
                ->find();
        if($user){        
            if (md5($data['password']) != $user['password']){
                $re_msg['msg'] = '用户名或密码不正确!';
            }else if ($user['status'] !== 1){
                $re_msg['msg'] = '该用户已经被禁用!';
            }else{
                $re_msg['success']  = 1;
                $secret = 'think';
                $time = time()+3600*24*7;
                $str = $user['mid'].":".$user['user_name'].":".$time.":".$secret;
                $sign = md5($str);
                $user_info = $user['mid'].":".$time.":".$sign;
                if($online){
                    Cookie::set('user_name', $data['username']);
                }else{
                    Cookie::set('user_name', "",-3600);
                }             
                Cookie::set('user_info', $user_info, $time);
                session("manage",$user);
            }
        }else{
            $re_msg['msg'] = '用户名或密码不正确!';
        }
        echo json_encode($re_msg);
    }

}