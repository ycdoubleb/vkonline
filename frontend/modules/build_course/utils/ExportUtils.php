<?php

namespace frontend\modules\build_course\utils;

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\Video;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ExportUtils
{
    /**
     * 初始化类变量
     * @var ActionUtils 
     */
    private static $instance = null;
    
    /**
     * 获取单例
     * @return ExportUtils
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ExportUtils();
        }
        return self::$instance;
    }
    
    /**
     * 导出单个课程信息
     * @param string $id 课程ID
     */
    public function exportFrame($id)
    {
        $course_name = Course::findOne($id)->name;
        $course_frame = $this->getCourseFrame($id);

        $this->saveCourseFrame($course_name, array_values($course_frame));
    }

    /**
     * 课程框架信息
     * @param string $id    课程ID
     * @return array
     */
    public function getCourseFrame($id)
    {
        $query = (new Query())
                ->from(['CourseNode' => CourseNode::tableName()])
                ->andFilterWhere(['CourseNode.course_id' => $id, 'CourseNode.is_del' => 0, 'Knowledge.is_del' => 0]);
        $query->leftJoin(['Knowledge' => Knowledge::tableName()], 'Knowledge.node_id = CourseNode.id');
        $query->leftJoin(['KnowledgeVideo' => KnowledgeVideo::tableName()], 'KnowledgeVideo.knowledge_id = Knowledge.id');
        
        /** 查询节点的知识点数量 */
        $knowNumQuery = clone $query;
        $knowNumQuery->addSelect(['CourseNode.id AS node_id', 'COUNT(Knowledge.id) AS know_num']);
        $knowNumQuery->groupBy(['CourseNode.id']);
        $knowNumQuery->orderBy(['CourseNode.sort_order' => SORT_ASC, 'Knowledge.sort_order' => SORT_ASC]);
        $knowNum = $knowNumQuery->all();
        /** 课程框架 */
        $nodeQuery = clone $query;
        $nodeQuery->select(['CourseNode.id AS node_id', 'CourseNode.name AS node_name', 'CourseNode.des AS node_des',
                            'Knowledge.id AS knowledge_id', 'Knowledge.name AS knowledge_name',
                            'Knowledge.des AS knowledge_des', 'KnowledgeVideo.video_id', 'Video.des AS video_des']);
        $nodeQuery->leftJoin(['Video' => Video::tableName()], 'Video.id = KnowledgeVideo.video_id');
        $nodeQuery->groupBy('Knowledge.id');
        $nodeQuery->orderBy(['CourseNode.sort_order' => SORT_ASC, 'Knowledge.sort_order' => SORT_ASC]);
        $course_frames = $nodeQuery->all();
        
        /** 组装数据 （把每个节点的知识点数组装到$course_frames） */
        $knowNums = ArrayHelper::index($knowNum, 'node_id');    //改node_id为key值
        foreach ($course_frames as $k => $val) {
            if(isset($knowNums[$val['node_id']])){
                $course_frames[$k] += ['rowspan' => $knowNums[$val['node_id']]['know_num']];
                unset($knowNums[$val['node_id']]);  //只设置相同个节点中的第一个
            }
        }

        return $course_frames;
    }

    /**
     * 导出课程框架信息
     * @param string $course_name        课程名称
     * @param array $course_frame        课程信息
     */
    private function saveCourseFrame($course_name, $course_frame)
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
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', '节点ID')->setCellValue('B1', '节点名称')
                ->setCellValue('C1', '节点描述')->setCellValue('D1', '知识点ID')->setCellValue('E1', '知识点名称')
                ->setCellValue('F1', '知识点简介(默认留空，将使用视频描述)')->setCellValue('G1', '视频ID');
        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);
        $spreadsheet->setActiveSheetIndex(0)->getRowDimension(1)->setRowHeight(28);
        // Miscellaneous glyphs, UTF-8
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A2', 'node.id')->setCellValue('B2', 'node.name')
                ->setCellValue('C2', 'node.des')->setCellValue('D2', 'knowledge.id')->setCellValue('E2', 'knowledge.name')
                ->setCellValue('F2', 'knowledge.des')->setCellValue('G2', 'video.id');
        $spreadsheet->getActiveSheet()->getStyle('A2:G2')->applyFromArray($styleArray); //居中
        
        $startRow = 3;
        foreach ($course_frame as $key => $data) {
            $columnIndex = 1; 
            $abcstartRow = $startRow + $key;
            $rowspan = isset($data['rowspan']) ? $data['rowspan'] : 0;
            $endRow = $abcstartRow + $rowspan - 1;
            if($rowspan != 0){
                $spreadsheet->getActiveSheet()->mergeCells("A$abcstartRow:A$endRow");
                $spreadsheet->getActiveSheet()->mergeCells("B$abcstartRow:B$endRow");
                $spreadsheet->getActiveSheet()->mergeCells("C$abcstartRow:C$endRow");
            }
            $keyRow = $key+$startRow;
            $spreadsheet->getActiveSheet()->getStyle("A$columnIndex:C$keyRow")->applyFromArray($styleArray);    //居中
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValueByColumnAndRow($columnIndex, $key+$startRow, $data['node_id'])
                ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, $data['node_name'])
                ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, Html::decode($data['node_des']))
                ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, $data['knowledge_id'])
                ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, $data['knowledge_name'])
                ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, Html::decode(
                                        !empty($data['knowledge_des']) ?$data['knowledge_des'] : $data['video_des']))
                ->setCellValueByColumnAndRow(++$columnIndex, $key+$startRow, $data['video_id']);          
        }
        
        //设置列宽
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(35);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(80);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        //设置背景颜色
        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFill()->getStartColor()->setARGB('808080');
        
        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle($course_name);
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename='.$course_name.'.xlsx');
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
