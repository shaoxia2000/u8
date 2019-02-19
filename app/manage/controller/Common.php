<?php

namespace app\manage\controller;

use think\Controller;
use think\Cache;
use think\Session;
class Common extends Controller
{
	public function _initialize()
	{
		if (request()->controller()!='Index' && request()->action()!='sign') {
			if (Session::get('area_id') == "") {
				$this->redirect('http://yg.dqedu.net');
			}
		}

	}
	
	// 获取同步数据
	public function loadSync()
	{
		$data = $this->doGet('sync');
		
		if (isset($data['errcode']) || $data === false) {
			// 服务器返回错误
			return;
		}
		
		// 整理返回数据
		$areas = [];
		$schools = [];
		$users = [];
		
		foreach ($data as $area) {
			
			// 加入地区
			$areas[$area['id']] = ['area_id' => $area['id'], 'area_name' => $area['name']];
			
			// 加地区管理员
			foreach ($area['users'] as $user) {
				$users[$user['id']] = ['user_id' => $user['id'], 'name' => $user['name'], 'school_id' => 0, 'area_id' => $area['id']];
			}
			
			foreach ($area['schools'] as $school) {
				// 加学校
				$schools[$school['id']] = ['schid' => $school['id'], 'schtype' => $school['stype'], 'schname' => $school['name'], 'areaid' => $area['id']];
				
				foreach ($school['users'] as $user) {
					$users[$user['id']] = ['user_id' => $user['id'], 'name' => $user['name'], 'school_id' => $school['id'], 'area_id' => $area['id'], 'is_del' => 0];
				}
				
			}
		}
		
		
		// 插入及更新数据
		foreach (['user', 'school', 'area'] as $table) {
			switch ($table) {
				case 'user':
					$key = 'user_id';
					break;
				case 'school':
					$key = 'schid';
					break;
				case 'area':
					$key = 'area_id';
					break;
			}
//            $key = $table.'_id';
			$arrayName = $table . 's';
			$arr = $$arrayName;
			
			$ids = array_column($arr, $key);
//            var_dump($ids);die;
			$model = model($table);

//            var_dump($model);die;
			// // 将所有的先软删除
			// $model::useGlobalScope(false)->where('id', 'neq', 0)->update(['is_del'=>1], false);
			
			if (!empty($ids)) {
				$ids = $model::where([$key => ['in', $ids]])->column('id', $key);
				if (!empty($ids)) {
					foreach ($ids as $key => $id) {
						$arr[$key]['id'] = $id;
					}
				}
			}
			
			$arr = array_values($arr);
			$model->saveAll($arr);
		}
		
		// 记录时间
		// \think\Cache::set('lastSyncTime',time(),60*60);
	}
	
	public function doGet($url, $data = [], $time = 2000)
	{
		
		$url = 'http://yg.dqedu.net/' . $url . '&' . http_build_query($data);
		
		$header = $this->getHeader();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $time);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $time);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res = curl_exec($ch);
		$errno = curl_errno($ch);
      
		if($res === false){
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            $info['errno'] = $errno;
            $info['error'] = curl_error($ch);
            file_put_contents(LOG_PATH . 'curl_log.'.date("Ymdhis").'.log', json_encode($info) ."\r\n", FILE_APPEND);
            return false;
        } else {
			$res = json_decode($res, true);
			return isset($res['err_code']) ? false : $res;
		}
	}
	
	public function getHeader()
	{
		$data = [];
		$data['appid'] = 'mvfj1521600901';
		$data['secretkey'] = '021f4c8cd613f3e7396e02e7b0ea1732';
		list($t1, $t2) = explode(' ', microtime());
		$data['timestamp'] = $t2 . ceil($t1 * 1000);
		
		ksort($data);
		$input = http_build_query($data);
		
		$arr = [];
		$arr['iv'] = base64_encode(substr($data['secretkey'], 0, 16));
		$arr['value'] = openssl_encrypt($input, 'AES-256-CBC', $data['secretkey'], 0, base64_decode($arr['iv']));
		$data['sign'] = base64_encode(json_encode($arr));
		
		unset($data['secretkey']);
		$data['version'] = 1;
		
		foreach ($data as $k => $v) {
			$data[] = $k . ':' . $v;
			unset($data[$k]);
		}
		
		return $data;
	}
	
	
}
