<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
class Login extends Controller
{
    public function index(){
        $this->assign('token',make_token('login.index'));
        return $this->fetch();
    }
    public function login(){
        $user = request()->post();
       //过滤非法字段
        $user['phone'] = safeText($user['phone']);
        $user['password'] = safeText($user['password']);
       //验证是否跨域
        if(!check_token($user['token'],'login.index')){
            exit;
        }
       //验证参数丢失
       //验证是否存在已登录账号
         $sessionid = session_id();
        if($sessionid){
            $resession = Db::name('user_session')->where('session_id',$sessionid)->find();
            if($resession){
             return doAjaxReturn(false,301);
            }
        }
        if($user['phone'] && $user['password'] && $user['token']){
            //验证字段
            if(!intval($user['phone'])){
                return doAjaxReturn(false,'请输入规范的手机号码!','',make_token('login.index'));
            }
            //验证账号是否存在
            $res =Db::name('admin_user')->where('username',$user['phone'])->find();
            if($res){
                //验证密码
                if(encryption_pass($user['password'],$res['salt_1'],$res['salt_2'])==$res['password']){
                    //检测成功
                    //检测是否存在同时登录多客户端
                    $useid = $res['id'];
                    $reses = Db::name('user_session')->where('userid',$useid)->find();
                    if($reses){
                        $delse = Db::table('lwh_user_session')->where('userid',$res['id'])->delete();
                        if(!$delse){
                            return doAjaxReturn(false,'该账户已登录!','',make_token('login.index'));
                        }
                    }
                    $res['login_time']=time();
                    \think\Session::set($res['id'],$res);
                    $session_id = session_id();
                    //存入数据库session信息
                    $prefix=config("database.prefix");
                    $insterSession = Db::execute("insert into {$prefix}user_session (`session_id`,`userid`) values('$session_id',$useid)");
                    if($insterSession){
                        insert_log($res['id'],$res['type'],'管理员登录','成功登录后台!');
                        return doAjaxReturn(true,'登录成功');
                    }else{
                        return doAjaxReturn(false,'请刷新页面重试',make_token('login.index'));
                    }

                                                    }else{
                    return doAjaxReturn(false,'密码错误!','',make_token('login.index'));
                }
            }else{
                return doAjaxReturn(false,'该账号不存在!','',make_token('login.index'));
            }
        }else{
            return doAjaxReturn(false,'请刷新页面重试!');
        }

    }
}
