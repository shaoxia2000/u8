<?php
$post = input('post.schid/a');

if (empty($post)) {
    $post = [0];
}

// 准备地区数据
$areas = \app\check\model\Area::where(array('area_id' => ['in', array_keys($post)]))->column('area_name', 'area_id');

// 字段及平均值公式;
$checkConfig = getCheckFieldConfig();

foreach ($post as $aid => $schids) {

    // table的thread使用格式
    $threadType = 2;

    $html = '';
    if (empty($schids)) {
        continue;
    }

    // 区的标题
    $html = flushHead($areas[$aid]);

    $schools = \app\check\model\School::where(['schid' => ['in', $schids]])->column('schname,schtype', 'schid');

    // 获取所有学校类型
    $types = array_unique(array_column($schools, 'schtype'));

    // if (count($types) > 1) {
    //     // 有两种以上学校
    //     $html .= flushSetThead(2);

    // } else {
    //     if (in_array('2', $types)) {
            // $threadType = 2;
            $html .= flushSetThead($threadType);
            // 都是初中
        // }
        //  elseif (in_array('3', $types)) {
        //     // 都是高中
        //     $html .= flushSetThead(3);
        // }
    // }

    foreach ($schids as $sid) {
        $avg       = [];
        $tableName = config('database.prefix') . 'students' . $sid;
        $type      = $schools[$sid]['schtype'];
        $avg = [];
        if (tableExits($tableName)) {
            
            $avg['nums']    = db()->table($tableName)->count();

            // 校验性别差异
            // 验证1-18位长度的身份证    护照也提示异常 详情中可以不予理会
            $errSexs = 0;
            $students = db()->table($tableName)->column('id_num,if(sex="男",1,0) sex','id');
           
            $avg['err_age'] = 0;
            foreach($students as $info){
                if(!$info['id_num']){
                    $errSexs++;
                    continue;
                }else{
                    $year = substr($info['id_num'], 6,4);
                    if($schools[$sid]['schtype'] == '1'){
                        if($year < '2009'){
                            $avg['err_age']++;
                        }
                    }elseif($schools[$sid]['schtype'] == '2'){
                        if($year < '2004'){
                            $avg['err_age']++;
                        }
                    }
                }

                if(strlen($info['id_num']) > 12 && intval(substr($info['id_num'],-2,1)) % 2 != $info['sex']){
                    $errSexs++;
                }
            }
            
            $avg['errSexs'] = $errSexs;

            if($type != '1'){
                $avg['warming'] = db()->table($tableName)->where($checkConfig[$type]['or'], null)
                    ->whereOr(function($query){
                        $query->where("(`chinese` = '0' and `smath` = '0' and `english` = '0')");
                    })
                    // ->whereOr($checkConfig[$type]['or'], 0)
                    ->whereOr($checkConfig[$type]['formula'])
                    ->count();
              
                $avg = array_merge($avg,db()->table($tableName)->field($checkConfig[$type]['avg'])->find());
         
            }
        }

        $html .= flushSetTr($schools[$sid]['schname'], $avg, $sid, $type, $threadType);
    }

    $html .= flushFoot($aid);
    echo $html;
    ob_flush();
    flush();
    ob_clean();
}

function flushHead($title)
{
    $str = '<div class="row">';
    $str .= '<div class="col-xs-12">';
    $str .= '<div class="box">';
    $str .= '<div class="box-header">';
    $str .= '<div class="box-name">';
    $str .= '<i class="fa fa-table"></i>';
    $str .= '<span>' . $title . '</span>';
    $str .= '</div>';
    $str .= '<div class="box-icons">';
    $str .= '<a class="collapse-link">';
    $str .= '<i class="fa fa-chevron-up"></i>';
    $str .= '</a>';
    $str .= '<a class="expand-link">';
    $str .= '<i class="fa fa-expand"></i>';
    $str .= '</a>';
    $str .= '<a class="close-link">';
    $str .= '<i class="fa fa-times"></i>';
    $str .= '</a>';
    $str .= '</div>';
    $str .= '<div class="no-move"></div>';
    $str .= '</div>';
    return $str;
}

function flushSetThead($type = 3)
{
    static $lang;
    $lang = getChineseByEnglish();

    $checkConfig = getCheckFieldConfig();
    $str         = '<div class="box-content no-padding">';
    $str .= '<table class="table table-striped table-bordered table-hover table-heading no-border-bottom">';
    $str .= '<thead>';
    $str .= '<tr>';
    $str .= '<th>单位</th>';
    $str .= '<th>类型</th>';
    $str .= '<th>平台现有人数</th>';

    // 写科目标题
    foreach ($checkConfig[$type]['fielda'] as $e) {
        $str .= '<th>' . $lang[$e] . '平均分</th>';
    }

    $str .= '<th>警告</th>';

    $str .= '</tr>';
    $str .= '</thead>';
    $str .= '<tbody>';
    return $str;
}

function flushSetTr($name, $avg, $sid, $schtype, $threadType)
{
    $str = '';
    $str .= '<tr>';
    // if($type == 2){
    $str .= '<td>' . $name . '</td>';
    $str .= '<td>' . getSchoolTypeForCheck($schtype) . '</td>';
    $str .= '<td>' . intval($avg['nums']) . '</td>';

    $checkConfig = getCheckFieldConfig();

    // 写对应平均分
    if ($threadType != $schtype) {
        // $dif = array_diff($checkConfig[3]['fielda'], $checkConfig[2]['fielda']);
        foreach ($checkConfig[$threadType]['fielda'] as $e) {
            // if (in_array($e, $dif)) {
                $str .= '<td>－</td>';
            // }
            // else {
            //     $str .= '<td>' . round($avg[$e], 3) . '</td>';
            // }
        }
    } else {
        // 一样直接按类型写
        foreach ($checkConfig[$threadType]['fielda'] as $e) {
            $str .= '<td>' . round($avg[$e], 3) . '</td>';
        }
    }
    
    $str .= '<td>';
    if ($avg['warming'] > 0 || $avg['errSexs'] > 0 || $avg['err_age']) {
        $errmsg = '查看异常';// (';
        // if($avg['warming'] > 0){
        //    $errmsg .= '成绩'.$avg['warming'].'处 ';
        // }
        // if($avg['errSexs'] > 0){
        //    $errmsg .= '　性别'.$avg['errSexs'].'处';
        // }
        // if($avg['err_age'] > 0){
        //     $errmsg .= '　身份证年份'.$avg['err_age'].'处';
        // }

        // $errmsg .= ')';
        $str .= '<a class="txt-danger" target="_blank" href="' . url('report/index', ['sid' => $sid]) . '">'.$errmsg.'</a>';
    }
    $str .= '</td>';
    $str .= '</tr>';
    return $str;
}

function flushFoot($aid)
{
    $str = '';
    $str .= '<tr>';
    $str .= '<td></td>';
    $str .= '</tr>';
    $str .= '</tbody>';
    $str .= '</table>';

    // 导出按钮
    $str .= '<div class="box-content"><div class="col-md-offset-11 text-right"><a target="_blank" href="' . url('report/average', array('aid' => $aid)) . '" class="btn btn-primary btn-xs"><i class="fa fa-download""></i>导出</a></div></div>';
    $str .= '</div>';
    $str .= '</div>';
    $str .= '</div>';
    $str .= '</div>';
    return $str;
}
?>