<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 14:26
 */

namespace app\common\model;


class Secondfb extends Base
{
	
	protected static function init()
	{
		Secondfb::event('before_delete', function ($secondfb) {
			$arts = Secondfb::find($secondfb->id);
			$imgdatas = unserialize($arts['pics']);
			foreach ($imgdatas as $k => $v) {
				$thumbpath = $_SERVER['DOCUMENT_ROOT'] . $v['src'];
				if (file_exists($thumbpath)) {
					@unlink($thumbpath);
				}
			}
		});
		
	}
	
	
	public static function Createsecondfb($data)
	{
		$field = ['name', 'schid', 'classid', 'fbtype', 'fcengid', 'sex', 'id_num', 'content', 'pics'];
		$res = self::create($data, $field);
		return $res->getData('id');
	}
	
	
}