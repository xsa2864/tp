<?php
namespace app\model;

use think\Model;
use think\facade\DB;

class User extends Model
{
	public function showInfo()
	{
		$rs = User::select();
		return $rs;
	}

}