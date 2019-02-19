<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 14:26
 */

namespace app\common\model;


class Tfaccess extends Base
{
	public static function CreateTfaccess($data)
	{
		$field = ['gid', 'fid', 'tgid'];
		$res = self::create($data, $field);
		return $res->getData('id');
	}
}

