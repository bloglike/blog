<?php
namespace app\admin\controller;
use think\Session;
use think\Db;
class Index extends Base
{
	public function index(){
		$seid = session_id();
		//获取用户id
		$userid = Db::name('user_session')->where('session_id',$seid)->find();
		$userid = $userid['userid'];
		if(Session::has($userid)){
			dump(Session::get($userid));
//			$this->assign('user',Session::get($userid));
			return $this->fetch();
		}
		
	}
		
}
