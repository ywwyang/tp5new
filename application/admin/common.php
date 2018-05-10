<?php
/**
 * 验证登录
 * @return bool|mixed
 */
function checkLogin(){
	if(session('?'.\think\Config::get('rbac.user_auth_key'))){
		return session(\think\Config::get('rbac.user_auth_key'));
	}else{
		return false;
	}
}


/**
 * 获取后台用户菜单
 * @param $menu_list 用户权限地址数组
 * @param $access_menu_list 所有菜单列表
 * @return array 返回权限用户菜单
 */
function get_menu($access_list,$access_menu_list){
	if(is_array($access_menu_list)){
		$parent_menus = array();
		$access_menu_list_new = array();
		if(is_array($access_list)){
			foreach($access_list as $key => $val){
				foreach($access_menu_list as $k => $v){
					if($val === $v['url']){
						$access_menu_list_new[] = $v;
					}
				}
			}

			foreach($access_menu_list_new as $key => $val){
				foreach($access_menu_list as $k => $v){
					if($val['pid'] === $v['id']){
						$parent_menus[$val['pid']] = $v;
					}
				}
			}
			$result = select_tree(array_merge($access_menu_list_new,$parent_menus),0);
		}else{
			$result = select_tree($access_menu_list,0);
		}
		return $result;
	}
}

/**
 * 重组菜单
 * @param $tree 用户拥有的菜单
 * @param int $id 菜单id 初始 为 0
 * @param int $lev
 * @param array $html
 * @return array
 */
function select_tree($tree, $id = 0, $lev = 0,$html=[]) {
	$new_tree1 = array();
	foreach ($tree as $key => $items) {
		if ($items['pid'] == $id) {
			$newid = $items['id'];
			$new_tree1[$items['id']] = $items;
			$new_tree1[$items['id']]['sub_menu'] = select_tree($tree, $newid);
		}
	}
	return $new_tree1;
}

/**
 * 获取后台用户权限
 * @param $access_list 如果该参数为 不是数组 则是超级管理员 返回 *
 * @return array 返回用户权限地址数组
 */
function get_access($access_list){
	if(is_array($access_list)){//非超级管理员
		$module = array();
		$url_arr = array();
		$url_action = array();
		foreach($access_list as $key => $val){
			if(0 === $val['pid'] || 1 === $val['level']){
				$module[] = $val;
			}
		}
		foreach($module as $k => $v){
			foreach($access_list as $k1 => $v1){
				if( $v['id'] == $v1['pid'] || 2 === $v1['level']){
					$url_arr[$v1['id']] = $v['name'] . '/' . $v1['name'];
					foreach($access_list as $k2 => $v2){
						if($v1['id'] == $v2['pid'] || 3 === $v1['level']){
							$url_action[] = $url_arr[$v1['id']] . '/' . $v2['name'];
						}
					}
				}
			}
		}
		return $url_action;
	}
	return '*';
}