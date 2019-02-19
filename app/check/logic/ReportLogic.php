<?php
namespace app\check\logic;

use app\check\model\School as SchoolModel;

class ReportLogic
{

    // 身份证所在列
    private static $IDRow        = 'B';
    private static $publicTitle  = ['name' => '姓名', 'id_num' => '身份证', 'sex'=>'性别','student_code' => '学籍号'];
    // private static $privateTitle = ['calc' => '平台计算结果'];
    private static $school;

    // 引入Excel;
    private static function loadExcel()
    {
        Vendor('PHPExcel.PHPExcel');
    }

    // 设置身份证所在列
    public static function setIdCol($col)
    {
        self::$IDRow = intToChr($col);
    }

    public function setPublicTitle($publicTitle){
    	self::$publicTitle = $publicTitle;
    }

    public function getPublicTitle(){
    	return self::$publicTitle;
    }

    public static function setSchool($sid){
        $school = SchoolModel::where(['schid' => $sid])->find();
        if($school){
        	self::$school = $school->toArray();
        	self::$school['config'] = getCheckFieldConfig(self::$school['schtype']);
        	self::$school['rule'] = explode('+', self::$school['config']['rule']);
            if(self::$school['schtype'] == '1'){
                unset(self::$publicTitle['student_code']);
            }
        }else{
        	self::$scholl = null;
        }

    }

    public static function getSchool(){
    	return self::$school;
    }

    /**
     * [reportSchoolAverage 导出学校分数相关校验结果]
     * @param  integer $sid 学校ID
     * @return excel
     */
    public static function reportSchoolAverage($sid = 0)
    {

    	if(empty(self::$school)) self::setSchool($sid);

        // 不是初中高中直接返回
        if (!in_array(self::$school['schtype'], [1, 2])) {
            return 'error';
        }

        $schtype = self::$school['schtype'];

        $checkConfig = self::$school['config'];

        // 加载Excel对象
        self::loadExcel();

        // 实例化excel
        $phpExcel = new \PHPExcel();

        // 活动sheet
        $sheet = $phpExcel->getActiveSheet();

        // sheet标题
        $sheet->setTitle(self::$school['schname']);

        // 记录行
        $row = 1;

        // 第一行写学校名儿
        // self::writeExcelTitle($sheet, $row, self::$school['schname'], count($checkConfig['fielda']) + 3);

        // 身份证行写文本
        $sheet->getStyle(self::$IDRow)->getNumberFormat()
            ->setFormatCode('@');

        self::writeAverageTitle($sheet, $row, $checkConfig['fielda']);

        // 查询数据
        $data = self::getSchoolAverAgeData($sid, 'all', $checkConfig);

        self::writeExcelContent($sheet, $row, $data);

        $fileName = self::$school['schname'] . '-' . date('YmdHis');

        self::downLoadExcel($fileName, $phpExcel);

    }

    /**
     * [writeExcelTitle 写第一行标题]
     * @param  [type] $sheet [description]
     * @param  [type] $title [description]
     * @param  [type] $num   [description]
     * @return [type]        [description]
     */
    private static function writeExcelTitle(&$sheet, &$row, $title, $num)
    {
        // 合并第一行  写名称
        $sheet->mergeCells('A' . $row . ':' . intToChr($num) . '1');
        $sheet->setCellValue('A' . $row, $title);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $row++;
    }

    /**
     * [writeAverageTitle description]
     * @param  [type] $sheet [description]
     * @param  [type] $row   [description]
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    private static function writeAverageTitle(&$sheet, &$row, $param)
    {
        // 第二行 写标题
        $col = 0;

        // 公共字段
        foreach (self::$publicTitle as $v) {
            $sheet->setCellValue(intToChr($col++) . $row, $v);
        }

        // 科目对应语言
        $lang = getChineseByEnglish();

        // 输入语言项
        foreach ($param as $p) {
            if($p != 'sex'){
                $sheet->setCellValue(intToChr($col++) . $row, $lang[$p]);
            }
        }

        if(self::$school['schtype'] != '1'){
            // 描述说明
            $sheet->setCellValue(intToChr($col++) . $row,'成绩不符或成绩有为0项');
        }
        // 描述说明
        $sheet->setCellValue(intToChr($col++) . $row,'性别与身份证不符');
        // 描述说明
        $sheet->setCellValue(intToChr($col) . $row,'身份证有疑问');

        $row++;

    }

    private static function downLoadExcel($fileName, $excel)
    {
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $objWriter = new \PHPExcel_Writer_Excel5($excel);
        $objWriter->save('php://output');
    }

    /**
     * [reportAreaAverage 导出区单位分数相关校验结果]
     * @param  integer $aid 区ID
     * @return excel
     */
    public static function reportAreaAverage($aid = 0, $sids = [])
    {
    	if(empty($sids)){
    		$sids = db()->school(['areaid'=>$aid,'schtype'=>['neq',3]])->column('schid');
    	}

    	// 糊弄人的
    	if(empty($sids)) return "所选学校有新的数据更新，请重新点击导出";

    	self::loadExcel();

    	$phpExcel = new \PHPExcel();

    	$sheetNum = 0;

    	foreach($sids as $sid){

    		self::setSchool($sid);

			$schtype = self::$school['schtype'];

			if(!in_array($schtype,[1,2])) continue;

			$phpExcel->createSheet();
			$phpExcel->setactivesheetindex($sheetNum++);

        	$checkConfig = self::$school['config'];

			$sheet = $phpExcel->getActiveSheet($sheetNum);
	        // sheet标题
	        $sheet->setTitle(self::$school['schname']);

	        // 记录行
        	$row = 1;

        	// 第一行写学校名儿
        	// self::writeExcelTitle($sheet, $row, self::$school['schname'], count($checkConfig['fielda']) + 3);
        	
        	/******样式********/
        	// $sheet->getStyle(self::$IDRow)->getNumberFormat()
       		// ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);

           	self::writeAverageTitle($sheet, $row, $checkConfig['fielda']);

           	// 查询所有数据
        	$data = self::getSchoolAverAgeData($sid, 'all', $checkConfig);

        	self::writeExcelContent($sheet, $row, $data);
    	}
    	
		$areaName = db('area')->where(['area_id'=>$aid])->value('area_name');
        $fileName = $areaName . '-' . date('YmdHis');

        self::downLoadExcel($fileName, $phpExcel);

    }

    /**
     * [getSchoolAverAgeData 获取学校分数相关数据]
     * @param  integer $sid 学校ID
     * @return array
     */
    public static function getSchoolAverAgeData($sid = 0, $type = 'all', $config = array())
    {
        $tableName = config('database.prefix') . 'students' . $sid;
        // 检查表是否存在
        if (!tableExits($tableName)) {
            return [];
        }

        $data = [];

        $field = $config['field'] . ',id';

        // 公共字段
        if (!empty(self::$publicTitle)) {
            $field .= ',' . implode(',', array_keys(self::$publicTitle));
        }

        $datas = db()->table($tableName)->column($field,'id');

        $errData = array();
        foreach($datas as $k => $data){
            switch ($data['sex']) {
                case '男':
                    $data['nsex'] = 1;
                    break;
                case '女':
                    $data['nsex'] = 0;
                    break;
                
                default:
                    $data['nsex'] = false;
                    break;
            }
        	$bol = false;
        	$cal = 0;
            if(self::$school['schtype'] != '1'){
                if(!self::checkNullOrZeroOrAve($data, $cal)){
                    $bol = true;
                    $data['err_ave'] = '　成绩不符'.$cal.'　';
                }
            }
        	if($data['nsex'] === false || ( strlen($data['id_num']) > 12  && intval ( substr( $data['id_num'],-2,1) )  % 2 != $data['nsex'] ) ){
        		$bol = true;
        		$data['err_sex'] = '　性别不符';
        	}
          	
          	$data['err_age'] = false;
          	if($data['id_num']){
            	$year = substr($data['id_num'], 6,4);
              	if(self::$school['schtype'] == '1'){
                	if($year < '2009'){
                   		$bol = true;
                    	$data['err_age'] = '　身份证有疑问';
                    }
                }elseif(self::$school['schtype'] == '2'){
                	if($year < '2004'){
                   		$bol = true;
                    	$data['err_age'] = '　身份证有疑问';
                    }
                }
            }
          
        	if($bol){
        		$errData[] = $data;
        		unset($datas[$k]);
        	}
        }

        $return = array();
        switch ($type) {
            case 'all':
            	$return = array_merge($errData, $datas);
                break;
            case 'warming':
                $return = $errData;
                break;
            case 'success':
                $return = $datas;
                break;
        }
        return $errData;

    }

    private function writeExcelContent(&$sheet, &$row, $data = [])
    {
    	if(empty($data)){
    		$sheet->setCellValue('A' . $row, '当前学校数据为空');
    		return;
    	}
        // 获取配置项
        $param = self::$school['config'];
        foreach ($data as $v) {
            $col = 0;
            // 公共字段
            foreach (array_keys(self::$publicTitle) as $name) {
              	if($name == 'id_num'){
                	$sheet->setCellValue(intToChr($col++) . $row, '\''.$v['id_num']);
                }else{
                	$sheet->setCellValue(intToChr($col++) . $row, $v[$name]);
                }
            }

            // 写数据
            foreach ($param['fielda'] as $p) {
                if($p != 'sex'){
                    $sheet->setCellValue(intToChr($col++) . $row, round($v[$p], 3));
                }
            }

            if(self::$school['schtype'] != '1'){
                $sheet->setCellValue(intToChr($col++) . $row, $v['err_ave'] ? '是' : '');
            }
            $sheet->setCellValue(intToChr($col++) . $row, $v['err_sex'] ? '是' : '');
            $sheet->setCellValue(intToChr($col) . $row, $v['err_age'] ? '是' : '');

            $row++;
        }
    }

    /**
     * [checkNullOrZeroOrAve description]
     * @param  array $data 数据集
     * @return boolean
     */
    private static function checkNullOrZeroOrAve($data, &$cal){
    	$calc = [];
		
      	$allZero = true;
    	// foreach(self::$school['rule'] as $key){
    	foreach(['chinese','smath','english'] as $key){
    		$point = 1;
    		if(strpos($key, '*') !== false){
    			list($name, $point) = explode('*', $key);
    		}else{
    			$name = $key;
    		}

    		// 0 || NULL
    		// if(!$data[$name]){
    		//	return false;
    		// }
          
			if($data[$name] > 0) $allZero = false;
    		$calc[] = $data[$name] * $point;
    	}
    	$cal = array_sum($calc);
    	if(bcsub($cal , round(floatval($data['zcj']),3)) > 0){
            return false;
        }
      	
        // 有一科不为0  这项成绩就有效
        // 前面有验证过总成绩
        if($allZero){
        	return false;
        }
      	
    	return true;
    }
}
