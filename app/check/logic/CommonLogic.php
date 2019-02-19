<?php
namespace app\check\logic;

use app\check\model\Area as AreaModel;
use app\check\model\School as SchoolModel;

class CommonLogic
{
    public static function getAllDetails()
    {
        $areas   = AreaModel::where(1)->column('area_name', 'area_id');
        $schools = SchoolModel::where(['schtype'=>['in',[1,2]]])->order('schtype asc')->column('schid,schtype,areaid,schname');
        $list    = array();

        foreach ($areas as $area_id => $area) {
            $list[$area_id]['area_id']   = $area_id;
            $list[$area_id]['area_name'] = $area;
        }

        unset($areas);

        $schoolType = array('1' => '小学', '2' => '初中', '3' => '高中');

        foreach ($schools as $k => $school) {
            $school['schoolType']              = $schoolType[$school['schtype']];
            $list[$school['areaid']]['data'][] = $school;
            unset($schools[$k]);
        }
        
        return array_values($list);
    }

    public static function getAreas()
    {
        return AreaModel::where(1)->column('area_name', 'area_id');
    }

    /**
     * [getAreaDetails 获取学校数据 初步统计]
     * @param  integer $aid   区id
     * @param  integer $schid 学校ID 默认查所有学校
     * @return array   结果集
     */
    public static function getAreaDetails($aid = 0, $schid = 0)
    {
        if (!$aid) {
            return array();
        }

        $where = array();
        if ($schid && is_string($schid)) {
            $schid = array($schid);
        } else {
            $schid = db()->name('school')->where(array('areaid' => $aid,'schtype'=>['in',[1,2]]))->column('schid');
            // $schid = db()->name('school')->field('schid')->where(array('areaid'=>$aid))->buildSql();
            // $schid .= ' as a';
        }

        // 获取分班数量及已输入教师数量
        $allClassNums = db()->name('teachergroup')->where(['schid' => array('in', $schid)])->column('tnum', 'schid');

        $allTeachNums = db()->name('teacher')->where(['schid' => array('in', $schid)])->group('schid')->column('count(1) as num', 'schid');

        // 获取所有学校
        $schools = SchoolModel::where(array('areaid' => $aid,'schtype'=>['neq','3']))->column('schid,schname,schtype');

        // 遍历
        foreach ($schools as $sid => $school) {

            // 表是否存在
            $studentTable = config('database.prefix') . 'students' . $sid;
            if (!tableExits($studentTable)) {
                continue;
            }

            $schools[$sid]['detail']      = self::getStudentsStatistics($studentTable, $school['schtype']);
            $schools[$sid]['teacher_num'] = $allTeachNums[$sid];
            $schools[$sid]['class_num']   = $allClassNums[$sid];
        }

        return $schools;
    }

    /**
     * [getStudentsStatistics 获取学生性别相关数据]
     * @param  string  $table 表名
     * @param  integer $type  类型
     * @return array
     */
    public static function getStudentsStatistics($table, $type)
    {
        $data            = array();
        $data            = db()->table($table)->column('sex');
        $return          = array();
        $return['count'] = count($data); // 总数
        $countSex        = array_count_values($data); // 统计男女出现次数
        $return['man']   = $countSex['男']; // 男
        $return['woman'] = $countSex['女']; // 女

        unset($countSex['男'], $countSex['女']);

        $return['warming'] = 0;

        // 如果统计出其他参数 且数据不为空;
        if (!empty($countSex) && !empty($data)) {
            $return['warming'] = 1;
        }

        return $return;
    }

    /**
     * [getCounts 地区数据]
     * @param  array  $areas 地区数据 areaid => areaname
     * @return 
     */
    public static function getCounts($areas = array())
    {
    	if(empty($areas)){
    		$areas = db()->name('area')->column('area_name','area_id');
    	}

    	$aids = array_keys($areas);
    	if(empty($aids)) return array();

    	$schids = db()->name('school')->where(array('areaid'=>array("in", $aids), 'schtype'=>['in',[1,2]]))->column('schid');
    	
    	// $allStudentNums = 0;
    	// foreach($schids as $sid){
    	// 	// 表是否存在
     //        $studentTable = config('database.prefix') . 'students' . $sid;
     //        if (!tableExits($studentTable)) {
     //            continue;
     //        }
     //        $allStudentNums += db()->table($studentTable)->count();
    	// }

    	$return = array();
    	// $return['student_num'] = $allStudentNums;
    	$return['school_num'] = count($schids);
    	return $return;
    }

}
