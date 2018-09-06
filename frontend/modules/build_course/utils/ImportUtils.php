<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\build_course\utils;

use common\models\vk\Teacher;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Description of ImportUtils
 *
 * @author Kiwi
 */
class ImportUtils {
    /**
     * 初始化类变量
     * @var ActionUtils 
     */
    private static $instance = null;
    
    /**
     * 获取单例
     * @return ImportUtils
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ImportUtils();
        }
        return self::$instance;
    }
    
    /**
     * 批量导入老师
     * @return array
     */
    public function batchImportTeacher()
    {
        $upload = UploadedFile::getInstanceByName('importfile');
        if($upload != null){
            $sexName = array_flip(Teacher::$sexName);   //性别
            $spreadsheet = IOFactory::load($upload->tempName); // 载入excel文件
            $sheet = $spreadsheet->getActiveSheet();    // 读取第一個工作表 
            $sheetdata = $sheet->toArray(null, true, true, true);   //转换为数组
            //以坐标为键值组装图像集
            $drawingDatas = [];
            foreach($sheet->getDrawingCollection() as $drawing){
                $drawingDatas[$drawing->getCoordinates()] = $drawing;
            }
            $sheetColumns = [];
            $datas = [];
            //获取组装的工作表数据
            for ($row = 3; $row <= count($sheetdata); $row++) {
                //组装对应数组值
                foreach ($sheetdata[2] as $key => $value) {
                    if(!empty($value)){ //值非空
                        $sheetColumns[$value] = $sheetdata[$row][$key];
                        //判断工作表坐标是否与图像集的坐标符合
                        if(isset($drawingDatas[$key . $row])){
                            $sheetColumns['coordinates'] = $drawingDatas[$key . $row];
                        }
                        //判断工作表的性别是否与定义的性别数组相符合
                        if(isset($sexName[$sheetdata[$row][$key]])){
                            $sheetColumns['sex'] = $sexName[$sheetdata[$row][$key]];
                        }
                        $sheetColumns['id'] = md5(time() . rand(1, 99999999));
                        $sheetColumns['customer_id'] = Yii::$app->user->identity->customer_id;
                        $sheetColumns['created_by'] = Yii::$app->user->id;
                        $sheetColumns['created_at'] = $sheetColumns['updated_at'] = time();
                    }
                }
                //判断每一行是否存在空值，若存在则过滤
                if(!empty(array_filter($sheetdata[$row]))){
                    $datas[] = $sheetColumns;
                }
            }
            
            return $this->batchSaveTeacher($datas);
        }
    }
    
    /**
     * 批量保存老师
     * @param array $dataProvider
     * @return array
     */
    protected function batchSaveTeacher($dataProvider)
    {
        $data_repeat = [];  //重复数据
        $data_exist = [];  //存在数据
        $data_insert = [];  //插入数据
        $is_success = false; //是否成功
        
        //excel传值上来的老师信息
        $teacher_name = ArrayHelper::getColumn($dataProvider, 'name');   //老师名称
        $teacher_sex = ArrayHelper::getColumn($dataProvider, 'sex');     //老师性别
        $teacher_job_title = ArrayHelper::getColumn($dataProvider, 'job_title');     //老师职称
        
        //获取重复的老师信息
        $repeat_name = array_diff_assoc($teacher_name, array_unique($teacher_name));  //重复老师名
        $repeat_sex = array_diff_assoc($teacher_sex, array_unique($teacher_sex));     //重复老师性别
        $repeat_job_title = array_diff_assoc($teacher_job_title, array_unique($teacher_job_title));   //重复老师职称
        
        //查询已经存在的老师
        $teacher = (new Query())->from(['Teacher' => Teacher::tableName()]);
        $teacher->select(['id', 'avatar', 'name', 'sex', 'job_title']);
        $teacher->where([
            'Teacher.name' => array_unique($teacher_name), 
            'Teacher.sex' => array_unique($teacher_sex)
        ]);
        $teacher_result = $teacher->all();
        //已经存在的数据
        foreach ($teacher_result as $value) {
            $key = $value['name'] . '_' . $value['sex'];
            $data_exist[$key] = $value;
        }
        
        //循环判断是否存在重复的数据和已经存在的数据
        foreach($dataProvider as $key => $data){
            $data_key = $data['name'] . '_' . $data['sex'];    //组装存在的key值
            //根据老师名、老师性别和老师职称判断
            if(isset($repeat_name[$key]) && isset($repeat_sex[$key]) && isset($repeat_job_title[$key])){
                $data_repeat[] = [
                    'avatar' => null,
                    'name' => $data['name'],
                    'sex' => $data['sex'],
                    'job_title' => $data['job_title'],
                    'reason' => '重复数据'
                ];
            //根据老师名和老师性别判断
            }else if(isset($data_exist[$data_key])){
                $data_exist[$data_key] += ['reason' => '已经存在'];
            }else{
                $data_insert[] = $data;
            }
        }
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            foreach ($data_insert as &$data_val) {
                if(!$is_success){
                    $fileName = $this->saveDrawing($data_val['coordinates'], $data_val['id'], Yii::getAlias('@frontend/web/upload/teacher/avatars/'));
                    $data_val['avatar'] = '/upload/teacher/avatars/' . $fileName . '?rand=' . rand(0, 1000);
                    unset($data_val['coordinates']);
                }
            }
            
            Yii::$app->db->createCommand()->batchInsert(Teacher::tableName(),
                isset($data_insert[0]) ? array_keys($data_insert[0]) : [], $data_insert)->execute();
            
            $trans->commit();  //提交事务
            $is_success = true;
            Yii::$app->getSession()->setFlash('success','导入成功！');
        }catch (Exception $ex) {
            $is_success = false;
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','导入失败::'.$ex->getMessage());
        }
        
        return [
            'repeat_total' => count($data_repeat),
            'exist_total' => count($data_exist),
            'insert_total' => $is_success ? count($data_insert) : 0,
            'dataProvider' => new ArrayDataProvider([
                'allModels' => array_merge($data_repeat, array_values($data_exist))
            ]),
        ];
    }
    
    /**
     * 保存绘图
     * @param MemoryDrawing $drawing
     * @param type $objectId    对象id
     * @param type $path        指定目录
     * @return string $myFileName  文件名
     */
    private function saveDrawing($drawing, $objectId, $path){
        $myFilePath = $this->fileExists($path);
        //判断一个$drawing是否是MemoryDrawing的实例
        if ($drawing instanceof MemoryDrawing) {
            ob_start(); //打开输出控制缓冲
            call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource()); //把第一个参数作为回调函数调用
            $imageContents = ob_get_contents(); //返回输出缓冲区的内容
            ob_end_clean(); //清空（擦除）缓冲区并关闭输出缓冲
            //判断图像的类型
            switch ($drawing->getMimeType()) {
                case MemoryDrawing::MIMETYPE_PNG :
                    $extension = 'png';
                    break;
                case MemoryDrawing::MIMETYPE_GIF:
                    $extension = 'gif';
                    break;
                case MemoryDrawing::MIMETYPE_JPEG :
                    $extension = 'jpg';
                    break;
            }
        } else {
            $zipReader = fopen($drawing->getPath(), 'r');   //打开文件或者 URL。
            $imageContents = '';
            //循环判断是否已到达文件末尾
            while (!feof($zipReader)) {
                $imageContents .= fread($zipReader, 1024);
            }
            fclose($zipReader); //关闭一个打开文件。
            $extension = $drawing->getExtension();  //后缀名
        }
        
        $myFileName = $objectId . '.' . $extension;     //文件名
        file_put_contents($myFilePath . $myFileName, $imageContents);   //写入文件到指定目录下
        
        return $myFileName;
    }

    /**
     * 检查目标路径是否存在，不存即创建目标
     * @param string $path    文件名
     * @return string
     */
    protected function fileExists($path) {
        if (!file_exists($path)) {
            mkdir($path);
        }
        return $path;
    }
}
