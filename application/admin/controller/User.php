<?php

namespace app\admin\controller;

use app\admin\model\AdminUser;
use think\Request;
use think\Session;

class User extends Base
{
    //登陆界面
    public function login()
    {
        $this->alreadyLogin();
        return view('login');
    }

    //监测系统登录
    public function checkLogin(Request $request)
    {
        $status = 0;
        $result = '';
        $data = $request->param();

        //创建验证规则
        $rule = [
            'username|用户名'=>'require',
            'passwd|密码'=>'require',
            'verify|验证码'=>'require|captcha'
        ];
        //自定义验证消息
        $msg = [
            'username'=>['require'=>'用户名不能为空，请检查'],
            'passwd'=>['require'=>'密码不能为空，请检查'],
            'verify'=>[
                'require'=>'验证码不能为空，请检查',
                'captcha'=>'验证码不正确，请检查'
            ]

        ];
        $result = $this->validate($data,$rule,$msg);
        if ($result === true){
            //构造查询条件
            $map = [
                'username'=>$data['username'],
                'passwd'=>md5($data['passwd'])
            ];
            $user = AdminUser::get($map);
            if ($user==null){
                $result="账户或者密码错误";
            }else{
                $status = 1;
                $result = '验证通过，点击[确定]跳转';
                Session::set('user_id',$user->id);
                Session::set('user_info',$user->getData());
            }
        }

        return ['status'=>$status,'message'=>$result];
    }


    //退出登录
    public function loginOut(){
        Session::delete('user_id');
        Session::delete('user_info');
        $this->success('注销登陆，正在返回','user/login');
    }


    //添加系统管理员界面
    public function adminAdd(){
        return view('admin-add');
    }


    //添加系统管理员
    public function addAdminUser(Request $request){
        $status = 0;
        $result = '';
        $data = $request->param();
        //创建验证规则
        $rule = [
            'username|用户名'=>'require',
            'passwd|密码'=>'require',
            'repass|密码校验'=>'require|confirm:passwd'
        ];
        //自定义验证消息
        $msg = [
            'username'=>['require'=>'用户名不能为空，请检查'],
            'passwd'=>['require'=>'密码不能为空，请检查'],
            'verify'=>[
                'require'=>'验证码不能为空，请检查',
                'confirm'=>'两次密码不一致，请检查'
            ]

        ];
        $result = $this->validate($data,$rule,$msg);
        if ($result === true){
            //插入数据
            $user = new AdminUser();
            $userinfo =$user->where('username', $data['username'])->find();

            if (!empty($userinfo)){
                $result = $userinfo['username']."已经存在，忘记密码请联系管理员";
            }else{
                $user->data([
                    'username'=>$data['username'],
                    'passwd'=>md5($data['passwd']),
                    'create_time'=>date("Y-m-d  H:i:s"),
                    'last_login_time'=>date("Y-m-d  H:i:s")
                ]);
                $user->allowField(true)->save();
                $status = 1;
                $result =  "添加管理员".$userinfo['username']."成功";
            }


        }

        return ['status'=>$status,'message'=>$result];
    }


    public function adminDelete($id){
        $status = 0;
        $result = "";
        if ($id!=1){
            AdminUser::destroy($id);
            $status = 1;
            $result = "数据删除成功";
        }else{
            $result = "此为系统管理员无权删除！";
        }
        return ['status'=>$status,'message'=>$result];
    }

    public function adminCate(){
        return view('admin-cate');
    }
    public function adminEdit($id){
        dump($id);
        $admin = AdminUser::get($id);
        $this->assign('admin',$admin);
        return view('admin-edit');
    }
    public function adminUpdate($id){
        $admin = AdminUser::get($id);

        return view('admin-edit');
    }
    public function adminList(){
        $adminlist = AdminUser::all();
        $this->assign('list',$adminlist);
        return view('admin-list');

    }
    public function adminRole(){
        return view('admin-role');
    }
    public function adminRule(){
        return view('admin-rule');
    }
}
