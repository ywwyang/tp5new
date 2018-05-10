<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 数据返回
 * @param  [int]    $code [结果码]
 * @param  [string] $msg  [接口要返回的提示信息]
 * @param  [array]  $data [接口要返回的数据]
 * @return [string]       [最终的json数据]
 */
function returnMsg($code, $msg = '', $data = [])
{
    // 组合数据
    $return_data['code'] = $code;
    $return_data['msg'] = $msg;
    if (!empty($data)) {
        $return_data['data'] = $data;
    }
    // 返回信息并终止脚本
    return $return_data;
}

/**
 * 调试使用
 * @param $data
 */
function get_re($data)
{
    echo "<pre>";
    var_dump(json_decode(json_encode($data), true));
    echo "</pre>";
}