<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/2
 * Time: 14:57
 */

namespace app\common\model;


class Admin extends Base
{
	protected static function init()
	{
		admin::event('before_delete', function ($admin) {
			AuthGroupAccess::where('uid', $admin->id)->delete();
		});
	}
	
	
	public static function CreateAdmin($data)
	{
		$r = self::where('name', trim($data['name']))->find();
		if ($r) {
			return;
		}
		$data['password'] = md5($data['password'] . "#kangtian#");
		$field = ['name', 'password','school_id'];
		$res = self::create($data, $field);
		if($res){
			$data['uid'] = $res->getData('id');
			Authgroupaccess::CreateAuthGroupAccess($data);
		}
		return $res->getData('id');
	}
	
}