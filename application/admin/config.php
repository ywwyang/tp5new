<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2020  All rights reserved
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 稻香 <214206783@qq.com>
// +----------------------------------------------------------------------
return [

    // +----------------------------------------------------------------------
    // | 模板参数替换
    // +----------------------------------------------------------------------
    'view_replace_str'    => array(
        '__CSS__'      => '/static/admin/css',
        '__JS__'       => '/static/admin/js',
        '__IMG__'      => '/static/admin/images',
        '__LAYUI__'    => '/static/admin/layui',
    ),

    // +----------------------------------------------------------------------
    // | Rbac权限配置
    // +----------------------------------------------------------------------
    'rbac'                 => [
        "user_auth_on"        => true,                  //是否开启权限验证(必配)
        "user_auth_type"      => 2,                     //验证方式（1、登录验证；2、实时验证）
        
        "user_auth_key"       => 'ljalsd_user',                  //用户认证识别号(必配)
        "admin_auth_key"      => 'admin',               //超级管理员识别号(必配)
        "user_auth_model"     => 'user',                //验证用户表模型 user
        'user_auth_gateway'   => 'admin/Login/index',   //用户认证失败，跳转URL
     
        'auth_pwd_encoder'    => 'md5',                 //默认密码加密方式

        "rbac_superadmin"     => 'admin',               //超级管理员 账号
     
        "not_auth_module"     => 'Login,Index',         //无需认证的控制器
        "not_auth_action"     => '',                    //无需认证的方法
     
        'require_auth_module' =>  '',                   //默认需要认证的模块
        'require_auth_action' =>  '',                   //默认需要认证的动作
     
        'guest_auth_on'       =>  false,                //是否开启游客授权访问
        'guest_auth_id'       =>  0,                    //游客标记
     
        "rbac_role_table"     => 'role',                //角色表名称(必配)
        "rbac_user_table"     => 'role_user',           //用户角色中间表名称(必配)
        "rbac_access_table"   => 'access',              //权限表名称(必配)
        "rbac_node_table"     => 'node',                //节点表名称(必配)
        "rbac_db_dsn"         => '',                    //数据库连接DSN
    ],

    // +----------------------------------------------------------------------
    // | 树形菜单图标
    // +----------------------------------------------------------------------
    'tree' => [
        'yes' => '/static/admin/images/1.png',
        'no' => '/static/admin/images/2.png'
    ],
];