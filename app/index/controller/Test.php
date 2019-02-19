<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/4/17
 * Time: 10:50
 */

namespace app\index\controller;


class Test
{
	public function test()
	{
		$zm = $this->IntToChr(8);
		halt($zm);
	}
	
	public function IntToChr($index, $start = 65)
	{
		$str = '';
		if (floor($index / 26) > 0) {
			$str .= IntToChr(floor($index / 26) - 1);
		}
		return $str . chr($index % 26 + $start);
	}
}