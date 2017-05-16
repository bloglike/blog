<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use think\Db;
class Base extends Controller
{
    protected function _initialize(){
        session_start();
        $seid = session_id();
        //判断该账号是否存在
        $user = Db::name('user_session')->where('session_id',$seid)->find();
        $userid = $user['userid'];
        if(!Session::has($user['userid'])){
            return $this->redirect('login/index');
        }else{
            //判断是否会话超时
            $thistime = time();
            $sess = Session::get($userid);
            if(time()-$sess['login_time']>15*60){
                //会话超时.15分钟内未访问会话
                $res = Db::table('lwh_user_session')->where('id',$user['id'])->delete();
                return $this->redirect('login/index');
            }
        }
    }

}
