<?php
$post = input('post.schid/a');

if (empty($post)) {
    $post = [0];
}

// 准备地区数据
$areas = \app\check\model\Area::where(array('area_id' => ['in', array_keys($post)]))->column('area_name', 'area_id');

foreach ($post as $aid => $schids) {

    if (empty($schids)) {
        continue;
    }

    // 已经分组完毕的学校ID集合
    // $schids = db('teachergroup')->where(array('schid' => ['in', $schids], 'gstatus'=>1))->column('schid');

    // 学校双胞胎开关
    $schoolSbt = db('setgroup')->where(array('schid' => ['in', $schids]))->column('sbt','schid');

    $html = '';

    // 区的标题
    $html = flushHead($areas[$aid]);

    $html .= flushSetThead();

    $schools = \app\check\model\School::where(['schid' => ['in', $schids]])->column('schname,schtype', 'schid');

    $dbPrefix = config('database.prefix');
    
    foreach ($schids as $sid) {
        $data = array();

        $hasStudent = false;
        if(!tableExits($dbPrefix.'students'.$sid)){
            $data['tablebums'] = 'students'.$sid.'表不存在';
        }else{
            $hasStudent = true;
            $data['tablebums'] = db('students'.$sid)->count();
        }

        $hasClass = false;
        if(!tableExits($dbPrefix.'classes'.$sid)){
            $data['fbnums'] = 'classes'.$sid.'表不存在';
        }else{
            $hasClass = true;
            $data['fbnums'] = db('classes'.$sid)->count();
        }

        // 双胞胎开关
        if($schools[$sid]['schtype'] != '3' && $hasStudent && ($schoolSbt[$sid] || true)){

            $data['sbt_sta'] = '开';
            $sbtIds = db('students'.$sid)->where('sbt is not null and sbt <> 0')->column('sbt','id');

            if(empty($sbtIds)){
                $data['sbt_msg'] = '无双胞胎绑定关系';
            }

            $arr = array();
            $sbtErr = false;

            // 整理数据  将根作为二维数据集合key
            foreach($sbtIds as $id => $sbt){
                if(!is_array($arr[$sbt]) /* || !in_array($sbt,$arr[$sbt])*/ ){
                    $arr[$sbt][] = $sbt;
                }
                $arr[$sbt][] = $id;
                $ids[] = $sbt;
                $ids[] = $id;

                // 出现超过二层深度指向
                if($sbtIds[$sbt]){
                    $sbtErr = true;
                }
            }

            if($sbtErr){
                $data['sbt_msg'] .= '<span class="txt-danger">双胞胎绑定关系异常</span>';
                continue;
            }

            if($hasClass){

                // 获取分组结果  只查询双胞胎关系的
                $sbts = db('classes'.$sid)->where('id','in', $ids)->column('cno', 'id');

                // 消解 重用
                unset($ids);

                // 整理数据 键 为班级号  值为班级内的双胞胎 不一定为通关系的
                $rsbt = array();
                foreach($sbts as $id => $no){
                    $rsbt[$no][] = $id;
                }

                // 循环双胞胎二维数组 键 跟双胞胎  值关系双胞胎(含自身)
                foreach($arr as $k => $v){

                    // 遍历结果 键班级号  值为班级内所有双胞胎
                    foreach($rsbt as $no => $ids){

                        // 根双胞胎在这个班 
                        if(in_array($k, $ids)){

                            // 1 取交集  2同时交集要与原集合相等
                            $dif = array_diff(array_intersect($ids, $v), $v);
                            if(!empty($dif)){
                                $data['sbt_msg'] .= '<span class="txt-danger">出现双胞胎不在同一班级情况</span>';
                                break 2;
                            }
                        }
                    }
                }

            }else{
                $data['sbt_msg'] .= '无classes'.$sid.'表 无法查询分组情况';
            }
            
        }else{
            $data['sbt_sta'] = '关';
        }

        $html .= flushSetTr($schools[$sid]['schname'], $schools[$sid]['schtype'], $data);
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

    $str  = '<div class="box-content no-padding">';
    $str .= '<table class="table table-striped table-bordered table-hover table-heading no-border-bottom">';
    $str .= '<thead>';
    $str .= '<tr>';
    $str .= '<th>单位</th>';
    $str .= '<th>类型</th>';
    $str .= '<th>平台现有人数</th>';
    $str .= '<th>分班结果总人数</th>';
    $str .= '<th>人数验证</th>';
    $str .= '<th>双胞胎开关</th>';
    $str .= '<th>双胞胎验证</th>';
    $str .= '</tr>';
    $str .= '</thead>';
    $str .= '<tbody>';
    return $str;
}

function flushSetTr($name, $schtype, $data)
{
    $str = '';
    $str .= '<tr>';
    // if($type == 2){
    $str .= '<td>' . $name . '</td>';
    $str .= '<td>' . getSchoolTypeForCheck($schtype) . '</td>';
    $str .= '<td>'.$data['tablebums'].'</td>';
    $str .= '<td>'.$data['fbnums'].'</td>';
    $str .= '<td>';
    if(!$data['tablebums'] || $data['fbnums'] != $data['tablebums']){
        $str .='<span class="txt-danger">不通过</span>';
    }else{
        $str .='<span class="txt-success">通过</span>';
    }
    $str .= '</td>';

    if($schtype == '3'){
        $str .= '<td>－</td>';
        $str .= '<td>－</td>';
    }else{
        $str .= '<td>';
        if($data['sbt_sta'] != 1){
            $str .='<span class="txt-danger">关闭</span>';
        }else{
            $str .='<span class="txt-success">开启</span>';
        }
        $str .= '</td>';

        $str .= '<td>';
        if($data['sbt_sta'] != 1){
            $str .= '－';
        }else{
            if($data['sbt_msg']){
                $str .='<span class="txt-danger">'.$data['sbt_msg'].'</span>';
            }else{
                $str .='<span class="txt-success">通过</span>';
            }
        }
        $str .= '</td>';
    }
    
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
    $str .= '</div>';
    $str .= '</div>';
    $str .= '</div>';
    $str .= '</div>';
    return $str;
}
?>