<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 14:26
 */

namespace app\common\model;


class Tgaccess extends Base
{
	public static function CreateTgaccess($data)
	{
		$field = ['gid', 'tgid', 'tid', 'tgname', 'isheader'];
		$res = self::create($data, $field);
		return $res->getData('id');
	}
}

