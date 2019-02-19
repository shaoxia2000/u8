<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9 0009
 * Time: 22:26
 */

namespace app\manage\controller;

use app\common\model\Teacher as TeacherModel;


class Excelteacher extends Common
{

    public function index()
    {
        if (request()->isPost()) {
            $pdata = input('post.');
            $file = request()->file('file');
            if ($file) {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    $exts = $info->getExtension(); //获取扩展名
                    $filename = 'public/uploads/' . $info->getSaveName(); //文件路径+文件名
                    vendor("PHPExcel.PHPExcel.PHPExcel");
                    vendor("PHPExcel.PHPExcel.IOFactory");
                    //创建PHPExcel对象，注意，不能少了\
                    $PHPExcel = new \PHPExcel();
                    //如果excel文件后缀名为.xls，导入这个类
                    if ($exts == 'xls') {
                        $PHPReader = \PHPExcel_IOFactory::createReader('Excel5');
                    } else if ($exts == 'xlsx') {
                        $PHPReader = \PHPExcel_IOFactory::createReader('Excel2007');
                    }
                    //载入文件
                    // $PHPReader = \PHPExcel_IOFactory::createReader('Excel5');
                    $PHPExcel = $PHPReader->load($filename);
                    //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
                    $currentSheet = $PHPExcel->getSheet(0);
                    //获取总列数
                    $allColumn = $currentSheet->getHighestColumn();
                    //获取总行数
                    $allRow = $currentSheet->getHighestRow();
                    ++$allColumn;
                    //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
                    for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                        //从哪列开始，A表示第一列
                        for ($currentColumn = 'A'; $currentColumn != $allColumn; $currentColumn++) {
                            //数据坐标
                            $address = $currentColumn . $currentRow;
                            //读取到的数据，保存到数组$arr中
                            $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
                        }
                    }
                    $result = $this->saveImport($data, $pdata['schid']);
                    if ($result) {
                        $remsg['code'] = 100;
                        $remsg['msg'] = "提交成功";
                    } else {
                        $remsg['code'] = 101;
                        $remsg['msg'] = "提交失败";
                    }
                    return json($remsg);
                } else {
                    return $file->getError();
                }
            }

        }


        return view();
    }


    public function saveImport($data, $schid)
    {
        foreach ($data as $k => $v) {
            $datanew[$k]['name'] = trim($v['A']);
            if (trim($v['B']) == "男") {
                $datanew[$k]['sex'] = 1;
            } else {
                $datanew[$k]['sex'] = 2;
            }
            if (trim($v['C']) == "班主任") {
                $datanew[$k]['duty'] = 1;
            } else {
                $datanew[$k]['duty'] = 2;
            }
	        $datanew[$k]['xueli'] = trim($v['D']);;
	        $datanew[$k]['xueke'] = trim($v['E']);;
            $datanew[$k]['schid'] = $schid;
            $datanew[$k]['school'] = trim($v['F']);
            $datanew[$k]['age'] = trim($v['G']);
            $datanew[$k]['teachage'] = trim($v['H']);
            $datanew[$k]['tel'] = trim($v['I']);
        }
        $tt = new TeacherModel();
        $result = $tt->saveAll($datanew);
        return $result;
    }


}