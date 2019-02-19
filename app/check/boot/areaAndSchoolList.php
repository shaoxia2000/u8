<?php
$areas = \app\check\model\Area::where(1)->column('area_name', 'area_id');
foreach ($areas as $area_id => $area_name) {
    echo '<div class="row">';
    echo '  <div class="col-sm-3">';
    echo '      <div class="checkbox">';
    echo '      <label><input type="checkbox" id="a' . $area_id . '">' . $area_name . '<i class="fa fa-square-o"></i></label></div>';
    echo '  </div>';
    echo '  <div class="col-md-1"><button type="button" class="btn btn-success" data-toggle="collapse" data-target="#areasub' . $area_id . '" aria-expanded="false" aria-controls="areasub' . $area_id . '">查看</button></div>';
    echo '</div>';
    echo '<div class="row col-md-offset-1 collapse" id="areasub' . $area_id . '">';
    $schools = \app\check\model\School::where(['areaid' => $area_id, 'schtype' => ['neq', 3]])->column('schname,schtype', 'schid');
    foreach ($schools as $schid => $school) {
        echo '<div class="col-sm-3">';
        echo '  <div class="checkbox"><label><input type="checkbox" pid="a' . $area_id . '" name="schid[' . $area_id . '][]" value="'.$schid.'">' . $school['schname'] . '  (' . getSchoolTypeForCheck($school['schtype']) . ')<i class="fa fa-square-o small"></i></label></div>';
        echo '</div>';
    }
    echo '</div>';
    ob_flush();
    flush();
    ob_clean();
}
?>