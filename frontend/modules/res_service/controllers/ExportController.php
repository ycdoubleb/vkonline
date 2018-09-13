<?php

namespace frontend\modules\res_service\controllers;

use common\models\vk\Course;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use yii\web\Controller;

class ExportController extends Controller
{
    /**
     * 导出单个课程信息
     * @param string $id 课程ID
     */
    public function actionSingle($id)
    {
        $categoryPath = BrandAuthorizeController::getCategoryFullPath($id);
        $courseInfos = BrandAuthorizeController::getFromCourseInfo($id);
        
        $courseName = Course::findOne(['id' => $id])->name;
        $courseFullPath = $categoryPath[$id] . '>' . $courseName;

        $this->saveCourseInfo($courseName, $courseFullPath, array_values($courseInfos));
    }

    /**
     * 导出多个课程信息
     * @param string $ids   多个课程ID
     */
    public function actionMore($ids)
    {
        $course_ids = array_filter(explode(',', $ids)); //切割字符串为数组并过滤空值
        
        $courseInfos = [];
        foreach ($course_ids as $key => $course_id) {
            $customer = Course::findOne(['id' => $course_id])->customer->name;
            $courseInfos += [
                $key => [
                    'cour_name' => Course::findOne(['id' => $course_id])->name,
                    'cour_path' => BrandAuthorizeController::getCategoryFullPath($course_id)[$course_id] . ' > ' . 
                                        Course::findOne(['id' => $course_id])->name,
                    'cour_infos' => array_values(BrandAuthorizeController::getFromCourseInfo($course_id))
                ]
            ];
        }

        $this->saveCourseInfos($customer, $courseInfos);
    }


    /**
     * 导出单个课程信息
     * @param string $courseName        课程名称
     * @param string $courseFullPath    课程分类路径
     * @param array $courseInfos        课程信息
     */
    private function saveCourseInfo($courseName, $courseFullPath, $courseInfos)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');
        // 设置上下居中
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', $courseFullPath)->mergeCells('A1:D1');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $spreadsheet->setActiveSheetIndex(0)->getRowDimension(1)->setRowHeight(28);
        // Miscellaneous glyphs, UTF-8
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A2', '环节')
                ->setCellValue('B2', '知识点')->setCellValue('C2', '视频清晰度')->setCellValue('D2', '视频路径');

        $startRow = 3;
        $spreadsheet->getActiveSheet()->getStyle('A2:D2')->applyFromArray($styleArray); //居中
        $spreadsheet->getActiveSheet()->getStyle("A3:C3")->applyFromArray($styleArray); //居中
        foreach ($courseInfos as $key => $data) {
            $columnIndex = 1; 
            $abstartRow = $startRow + $key;
            $rowspan = isset($data['rowspan']) ? $data['rowspan'] : 0;
            $endRow = $abstartRow + $rowspan * 5 - 1;
            if($rowspan != 0){
                $spreadsheet->getActiveSheet()->mergeCells("A$abstartRow:A$endRow");
            }
            $bendRow = $abstartRow + 4;
            $spreadsheet->getActiveSheet()->mergeCells("B$abstartRow:B$bendRow");
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow($columnIndex, $key+$startRow, $data['node_name'])
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, $data['knowledge_name'])
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, '原视频')
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, isset($data['video_source']['source_video']) ? $data['video_source']['source_video'] : '无')
                    ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+1, '流畅')
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+1, isset($data['video_source']['ld_video']) ? $data['video_source']['ld_video'] : '无')
                    ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+2, '标清')
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+2, isset($data['video_source']['sd_video']) ? $data['video_source']['sd_video'] : '无')
                    ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+3, '高清')
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+3, isset($data['video_source']['hd_video']) ? $data['video_source']['hd_video'] : '无')
                    ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+4, '超清')
                    ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+4, isset($data['video_source']['fd_video']) ? $data['video_source']['fd_video'] : '无');
            $startRow += 4;            
            $keyRow = $key+$startRow;
            $spreadsheet->getActiveSheet()->getStyle("A$columnIndex:C$keyRow")->applyFromArray($styleArray);    //居中
        }

        //设置列宽
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(160);
        $spreadsheet->getActiveSheet()->getStyle('A2:D2')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        //设置背景颜色
        $spreadsheet->getActiveSheet()->getStyle('A2:D2')->getFill()->setFillType(Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A2:D2')->getFill()->getStartColor()->setARGB('808080');
        
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle($courseName);
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename='.$courseName.'.xlsx');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 导出多个课程信息
     * @param string $customer          客户集团名称
     * @param array $courseInfos        课程信息
     */
    private function saveCourseInfos($customer, $courseInfos)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        
        // Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');
        // 设置上下居中
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        foreach ($courseInfos as $index => $course) {
            if($index != 0){
                // Create a new worksheet, after the default sheet
                $spreadsheet->createSheet();
            }
            // Add some data
            $spreadsheet->setActiveSheetIndex($index)
                ->setCellValue('A1', $course['cour_path'])->mergeCells('A1:D1');
            $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
            $spreadsheet->setActiveSheetIndex($index)->getRowDimension(1)->setRowHeight(28);
            // Miscellaneous glyphs, UTF-8
            $spreadsheet->setActiveSheetIndex($index)->setCellValue('A2', '环节')
                    ->setCellValue('B2', '知识点')->setCellValue('C2', '视频清晰度')->setCellValue('D2', '视频路径');

            $startRow = 3;
            $spreadsheet->getActiveSheet()->getStyle('A2:D2')->applyFromArray($styleArray); //居中
            $spreadsheet->getActiveSheet()->getStyle("A3:C3")->applyFromArray($styleArray); //居中
            foreach ($course['cour_infos'] as $key => $data) {
                $columnIndex = 1; 
                $abstartRow = $startRow + $key;
                $rowspan = isset($data['rowspan']) ? $data['rowspan'] : 0;
                $endRow = $abstartRow + $rowspan * 5 - 1;
                if($rowspan != 0){
                    $spreadsheet->getActiveSheet()->mergeCells("A$abstartRow:A$endRow");
                }
                $bendRow = $abstartRow + 4;
                $spreadsheet->getActiveSheet()->mergeCells("B$abstartRow:B$bendRow");
                $spreadsheet->setActiveSheetIndex($index)
                        ->setCellValueByColumnAndRow($columnIndex, $key+$startRow, $data['node_name'])
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, $data['knowledge_name'])
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, '原视频')
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, isset($data['video_source']['source_video']) ? $data['video_source']['source_video'] : '无')
                        ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+1, '流畅')
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+1, isset($data['video_source']['ld_video']) ? $data['video_source']['ld_video'] : '无')
                        ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+2, '标清')
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+2, isset($data['video_source']['sd_video']) ? $data['video_source']['sd_video'] : '无')
                        ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+3, '高清')
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+3, isset($data['video_source']['hd_video']) ? $data['video_source']['hd_video'] : '无')
                        ->setCellValueByColumnAndRow(--$columnIndex, $key+$startRow+4, '超清')
                        ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow+4, isset($data['video_source']['fd_video']) ? $data['video_source']['fd_video'] : '无');
                $startRow += 4;            
                $keyRow = $key+$startRow;
                $spreadsheet->getActiveSheet()->getStyle("A$columnIndex:C$keyRow")->applyFromArray($styleArray);    //居中
            }

            //设置列宽
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(160);
            $spreadsheet->getActiveSheet()->getStyle('A2:D2')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
            //设置背景颜色
            $spreadsheet->getActiveSheet()->getStyle('A2:D2')->getFill()->setFillType(Fill::FILL_SOLID);
            $spreadsheet->getActiveSheet()->getStyle('A2:D2')->getFill()->getStartColor()->setARGB('808080');

            // Rename worksheet
            $spreadsheet->getActiveSheet()->setTitle($course['cour_name']);
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);
        
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename='.$customer.'.xlsx');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

}
