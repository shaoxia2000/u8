<?php

function tableExits($tableName)
{
    $sql = 'SHOW TABLES LIKE \'' . $tableName . '\'';
    $has = db()->query($sql);
    return !empty($has);
}

function getSchoolTypeForCheck($type)
{
    $arr = ['1' => '小学', '2' => '初中', '3' => '高中'];
    return $arr[$type];
}

/**
 * [getCheckFieldConfig 简单做一些配置 可以考虑整理到数据库]
 * @return array
 */
function getCheckFieldConfig($type = 0)
{
    /**初中数据**/
    $checkConfig['2']['field']  = 'zcj,chinese,smath,english';
    $checkConfig['2']['fielda'] = explode(',', $checkConfig['2']['field']);
    foreach ($checkConfig['2']['fielda'] as $v) {
        $checkConfig['2']['avg'][] = 'AVG(' . $v . ') ' . $v;
    }
    $checkConfig['2']['avg']     = implode(',', $checkConfig['2']['avg']);
    $checkConfig['2']['or']      = str_replace(',', '|', $checkConfig['2']['field']);
    $checkConfig['2']['year']    = '(substring(id_num,,6,4) < 2009 or id_num is null or id_num = "")';

    // 总分计算公式
    $checkConfig['2']['rule'] = 'CAST(chinese as DECIMAL(6,2) ) +  CAST(english as DECIMAL(6,2) ) +  CAST(smath as DECIMAL(6,2) )';
    // $checkConfig['2']['rule'] = 'chinese+smath+english';
    $checkConfig['2']['formula'] = '(' . $checkConfig['2']['rule'] . ')  <> zcj';


    /**高中数据**/
    $checkConfig['3']['field']  = $checkConfig['2']['field'] . ',physics,geography,chemistry,biologic,history,politics,sports';
    $checkConfig['3']['fielda'] = explode(',', $checkConfig['3']['field']);
    foreach ($checkConfig['3']['fielda'] as $v) {
        $checkConfig['3']['avg'][] = 'AVG(' . $v . ') ' . $v;
    }
    $checkConfig['3']['avg']     = implode(',', $checkConfig['3']['avg']);
    $checkConfig['3']['or']      = str_replace(',', '|', $checkConfig['3']['field']);

    // 总分计算公式
    //                            语文    数学   英语    物理    化学      地理           生物      历史         政治    体育
    $checkConfig['3']['rule'] = 'chinese+smath+english+physics+chemistry+geography*0.3+biologic*0.3+history*0.3+politics*0.3+sports';
    $checkConfig['3']['formula'] = '(' .  str_replace(',', '+',substr($checkConfig['3']['field'],4)) . ')  <> zcj';
    $checkConfig['3']['year']    = '(substring(id_num,,6,4) < 2004 or id_num is null or id_num = "")';

  	$checkConfig[1]['fielda'] = ['sex'];
    $checkConfig[1]['field'] = 'sex';

    if ($type) {
        return $checkConfig[$type];
    }

    return $checkConfig;
}

/**
 * [getChineseByEnglish 中英文对照]
 * @return arrray
 */
function getChineseByEnglish($name = '')
{
    $checkLang              = array();
    $checkLang['zcj']       = '总成绩';
    $checkLang['chinese']   = '语文';
    $checkLang['smath']     = '数学';
    $checkLang['english']   = '英语';
    $checkLang['physics']   = '物理';
    $checkLang['chemistry'] = '化学';
    $checkLang['biologic']  = '生物';
    $checkLang['history']   = '历史';
    $checkLang['politics']  = '政治';
    $checkLang['sports']    = '体育';
    if($name){
        return $checkLang[$name];
    }
    return $checkLang;
}

// /**
//  * 数字转字母 （类似于Excel列标）
//  * @param Int $index 索引值
//  * @param Int $start 字母起始值
//  * @return String 返回字母
//  */
// function intToChr($index, $start = 65)
// {
//     $str = '';
//     if (floor($index / 26) > 0) {
//         $str .= IntToChr(floor($index / 26) - 1);
//     }
//     return $str . chr($index % 26 + $start);
// }
