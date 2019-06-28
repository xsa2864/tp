<?php
namespace app\job;

use think\queue\Job;
use think\facade\DB;

class Job1{
    
    public function fire(Job $job, $data){
    
        //....这里执行具体的任务 
        $datas['UserName'] = 'hb';
        $datas['InTime'] = date("Y-m-d H:i:s",time());
        $rs = DB::table("t_user")->insert($datas);
        echo $data."<br>";
        if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
            echo $job->attempts();
            // if($rs){
            //     $job->delete();
            // }
        }
        echo $job->attempts()."===<br>";
        
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        // $job->delete();
        
        // 也可以重新发布这个任务
        $job->release(3); //$delay为延迟时间
          
    }
    
    public function failed($data){
        echo 'failed';
        // ...任务达到最大重试次数后，失败了
    }

}
