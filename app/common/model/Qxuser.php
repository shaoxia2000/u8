<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 14:26
 */

namespace app\common\model;

use think\Image;


class Qxuser extends Base
{
	protected static function init()
	{
		Qxuser::event('before_insert', function ($qxuser) {
			if ($_FILES['thumb']['tmp_name']) {
				$file = request()->file('thumb');
				$info = $file->move(ROOT_PATH . 'public' . DS . 'groupphoto');
				if ($info) {
					$thumb = 'groupphoto' . '/' . $info->getSaveName();
					$thumbs = ROOT_PATH . 'public' . DS .'groupphoto' . '/' . $info->getSaveName();
					$image = Image::open($thumbs);
					$image->thumb(157, 219)->save($thumbs);
					$qxuser['thumb'] = $thumb;
				}
			}
		});
		
		
		Qxuser::event('before_update', function ($qxuser) {
			if ($_FILES['thumb']['tmp_name']) {
				$arts = Qxuser::find($qxuser->id);
				$thumbpath = $_SERVER['DOCUMENT_ROOT'] . '/public/' . $arts['thumb'];
				if (file_exists($thumbpath)) {
					@unlink($thumbpath);
				}
				$file = request()->file('thumb');
				$info = $file->move(ROOT_PATH . 'public' . DS . 'groupphoto');
				if ($info) {
					$thumb = 'groupphoto' . '/' . $info->getSaveName();
					$thumbs = ROOT_PATH . 'public' . DS .'groupphoto' . '/' . $info->getSaveName();
					$image = Image::open($thumbs);
					$image->thumb(157, 219)->save($thumbs);
					$qxuser['thumb'] = $thumb;
				}
				
			}
		});
		
		Qxuser::event('before_delete', function ($qxuser) {
			
			$arts = Qxuser::find($qxuser->id);
			$thumbpath = $_SERVER['DOCUMENT_ROOT'] . '/public/' . $arts['thumb'];
			if (file_exists($thumbpath)) {
				@unlink($thumbpath);
			}
		});
		
	}
	
	
	public static function Createqxuser($data)
	{
		$field = ['qid', 'name', 'tel', 'type', 'thumb','areaid'];
		$res = self::create($data, $field);
		return $res->getData('id');
	}
	
}