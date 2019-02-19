<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 14:26
 */

namespace app\common\model;
use think\Image;

class Teacher extends Base
{
	protected static function init()
	{
		Teacher::event('before_insert', function ($teacher) {
			if ($_FILES['thumb']['tmp_name']) {
				$file = request()->file('thumb');
				$info = $file->move(ROOT_PATH . 'public' . DS . 'groupphoto');
				if ($info) {
					$thumb = 'groupphoto' . '/' . $info->getSaveName();
					$thumbs = ROOT_PATH . 'public' . DS .'groupphoto' . '/' . $info->getSaveName();
					$image = Image::open($thumbs);
					$image->thumb(150, 150)->save($thumbs);
					$teacher['thumb'] = $thumb;
				}
			}
		});
		
		
		Teacher::event('before_update', function ($teacher) {
			if ($_FILES['thumb']['tmp_name']) {
				$arts = Teacher::find($teacher->id);
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
					$image->thumb(150, 150)->save($thumbs);
					$teacher['thumb'] = $thumb;
				}
				
			}
		});
		
		Teacher::event('before_delete', function ($teacher) {
			
			$arts = Teacher::find($teacher->id);
			$thumbpath = $_SERVER['DOCUMENT_ROOT'] . '/public/' . $arts['thumb'];
			if (file_exists($thumbpath)) {
				@unlink($thumbpath);
			}
			$map['tid'] = ['eq', $arts['id']];
			Tgaccess::where($map)->delete();
			
		});
		
	}
	
	public static function Createteacher($data)
	{
		$field = ['name', 'sex', 'duty','xueke','xueli', 'schid', 'school', 'age', 'teachage', 'tel', 'thumb'];
		$res = self::create($data, $field);
		return $res->getData('id');
	}
	
	
}