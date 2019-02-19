<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/2
 * Time: 14:57
 */

namespace app\common\model;


class Authgroupaccess extends Base
{
	
	protected $table = 'bk_auth_group_access';
	
	public static function CreateAuthGroupAccess($data)
	{
		$field = ['uid', 'group_id'];
		self::create($data, $field);
	}
	
}