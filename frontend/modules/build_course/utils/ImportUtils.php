<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\build_course\utils;

use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\utils\StringUtil;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
     * 批量导入视频
     * @param integer $requestMode   格式：0是post，1是ajax格式
     * @param array $post
     * @return json|array
     */
    public function importVideo($request_mode, $post)
    {
        $upload = UploadedFile::getInstanceByName('importfile');
        if($upload != null){
            $spreadsheet = IOFactory::load($upload->tempName); // 载入excel文件
            $sheet = $spreadsheet->getActiveSheet();    // 读取第一個工作表 
            $sheetdata = $sheet->toArray(null, true, true, true);   //转换为数组
            $sheetColumns = [];
            $dataProvider = [];
            //获取组装的工作表数据
            for ($row = 3; $row <= count($sheetdata); $row++) {
                //组装对应数组值
                foreach ($sheetdata[2] as $key => $value) {
                    if(!empty($value)){ //值非空
                        $sheetColumns[$value] = $sheetdata[$row][$key];
                    }
                }
                //判断每一行是否存在空值，若存在则过滤
                if(!empty(array_filter($sheetdata[$row]))){
                    $dataProvider[] = $sheetColumns;
                }
            }
            foreach ($dataProvider as &$data) {
                $data['video.dirid'] = $this->checkVideoDirExists($data['video.dir']);
                $data['teacher.data'] = $this->checkTeacherExists(['name' => $data['teacher.name']], true);
                $data['video.tagsid'] = $this->checkTagsExists($data['video.tags']);
            }
        }
        //如果是ajax则返回json格式的数据
        if($request_mode){
            return $this->saveVideo($post);
        }else{
            return [
                'repeat_total' => 0,
                'exist_total' => 0,
                'insert_total' => count($dataProvider),
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $dataProvider,
                ]),
            ];
        }       
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
            $dataProvider = [];
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
                    }
                }
                //判断每一行是否存在空值，若存在则过滤
                if(!empty(array_filter($sheetdata[$row]))){
                    $dataProvider[] = $sheetColumns;
                }
            }
            
            return $this->batchSaveTeacher($dataProvider);
        }
    }

    /**
     * 批量保存老师
     * @param array $datas
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
        $teacher_result = $this->checkTeacherExists([
            'name' => array_unique($teacher_name), 
            'sex' => array_unique($teacher_sex),
        ]);
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
                    $data_val['id'] = md5(time() . rand(1, 99999999));
                    $fileName = $this->saveDrawing($data_val['coordinates'], $data_val['id'], Yii::getAlias('@frontend/web/upload/teacher/avatars/'));
                    $data_val['avatar'] = '/upload/teacher/avatars/' . $fileName . '?rand=' . rand(0, 1000);
                    $data_val['customer_id'] = Yii::$app->user->identity->customer_id;
                    $data_val['des'] = Html::encode($data_val['des']);
                    $data_val['created_by'] = Yii::$app->user->id;
                    $data_val['created_at'] = $data_val['updated_at'] = time();
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
    
    protected function saveVideo($post)
    {
        var_dump($post);exit;
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
     * 保存视频目录
     * @param string $name  名称
     * @param integer $parent_id   上一级id
     * @return integer $id  分类id
     */
    protected function saveVideoDir($name, $parent_id = 0)
    {
        $category = new UserCategory([
            'name' => $name, 'parent_id' => $parent_id, 
            'created_by' => \Yii::$app->user->id
        ]);
        
        $category->level = $category->parent_id == 0 ? 1 : UserCategory::getCatById($category->parent_id)->level + 1;
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($category->save()){
                $category->updateParentPath();      //更新路径
                $trans->commit();  //提交事务
            }
            UserCategory::invalidateCache();    //清除缓存    
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
        }
        
        return $category->id;
    }
    
    /**
     * 检查视频目录是否存在
     * @param string $video_dirs    视频目标
     * @return integer $dir_id 目录id
     */
    protected function checkVideoDirExists($video_dirs)
    {
        $dirs = explode(">", $video_dirs);  //目录结构
        $dirCount = count($dirs);   //计算上传的目录个数
        array_walk_recursive($dirs, function(&$val){$val = trim($val);});   //过滤数组中值两端的空格
        
        //查询已存在的目录
        $existCategorys = [];   //已存在目录
        $userCategory = (new Query())->from(['UserCategory' => UserCategory::tableName()]);
        $userCategory->select(['UserCategory.id', 'UserCategory.parent_id']);
        $userCategory->where(['UserCategory.name' => $dirs]);
        $userCategory->andWhere(['or', ['UserCategory.created_by' => \Yii::$app->user->id], ['UserCategory.is_public' => 1]]);
        $userCategory->orderBy(['UserCategory.path' => SORT_ASC]);
        $categorys = $userCategory->all();
        //获取需要的已存在目录
        foreach ($categorys as $cat) {
            foreach ($dirs as $dir) {
                //上级目录名
                $parentname = UserCategory::getCatById($cat['parent_id'] > 0 ? $cat['parent_id'] : $cat['id'])->name;
                //上传目录的值和存在的上级目录名相同，则返回id
                if($dir == $parentname){
                    $existCategorys[] = $cat['id'];
                }
            }
        }
        //计算已存在的目录个数
        $catCount = count($existCategorys);     
        
        $dir_id = 0;    //目录id
        //如果已存在的目录等于上传的目录，并且已存在的目录大于0，则目录id为最后一个已存在的目录id
        if($catCount == $dirCount && $catCount > 0){
            $dir_id = end($existCategorys);
        //如果已存在的目录小于上传的目录，并且已存在的目录大于0，则目录id为新建的目录id*/
        }else if($catCount < $dirCount && $catCount > 0 ){
            foreach ($dirs as $key => $name) {
                if(isset($existCategorys[$key])) continue;
                $dir_id = $this->saveVideoDir($name, $existCategorys[$key - 1]);
            }
        //如果已存在的目录等于0，则创建目录结构并且目录id为新建的目录目录结构等级最高的那个id
        }else if($catCount == 0){
            $dir_id = $this->saveVideoDir($dirs[0]);
            for($i = 1; $i < $dirCount; $i++){
                if($dir_id == null) break;
                $dir_id = $this->saveVideoDir($dirs[$i], $dir_id);
            }
        }
       
        return $dir_id;
    }
    
    /**
     * 检查老师是否存在
     * @param array|string $condition  条件
     * @param boolean $key_to_value    键值对
     * @return array
     */
    protected function checkTeacherExists($condition, $key_to_value = false)
    {
        //根据条件查询已存在老师
        $teacher = (new Query())->from(['Teacher' => Teacher::tableName()]);
        $teacher->select(['id', 'avatar', 'name', 'sex', 'job_title']);
        $teacher->where($condition);
        $teacher->andWhere(['Teacher.is_del' => 0]);
        $teacher->andWhere([
            'or', ['Teacher.created_by' => \Yii::$app->user->id], ['is_certificate' => 1]
        ]);
        $teacher_results = $teacher->all();
        
        if($key_to_value){
            $teacherFormat = [];
            foreach ($teacher_results as $teacher_data) {
                $teacherFormat[$teacher_data['id']] = [
                    'id' => $teacher_data['id'],
                    'avatar' => StringUtil::completeFilePath($teacher_data['avatar']), 
                ];
            }
            return $teacherFormat;
        }else{
            return $teacher_results;
        }
    }
    
    /**
     * 检查标签是否已存在
     * @param string $video_tags    视频标签
     * @return array    返回已存在和新插入的标签id
     */
    protected function checkTagsExists($video_tags)
    {
        $tagIds = [];   //保存插入表返回的所有id
        /* 判断分割符的格式 */
        if(strpos($video_tags ,"、")){  
            $videoTags =  explode("、", $video_tags);  
        }else if(strpos($video_tags ,'，')){  
            $videoTags =  explode("，", $video_tags);  
        }else{
            $videoTags = explode(',', $video_tags);
        }
        //查询已存在的标签
        $tags = (new Query())->from(['Tags' => Tags::tableName()]);
        $tags->select(['id', 'name'])->where(['name' => $videoTags]);
        $tag_results = $tags->all();
        $tag_ids = ArrayHelper::getColumn($tag_results, 'id');    //获取已存在的id
        $tag_names = ArrayHelper::getColumn($tag_results, 'name'); //获取已存在的name
        //保存不存在的标签
        foreach ($videoTags as $tags_name) {
            if(!in_array($tags_name, $tag_names)){
                $tagModel = new Tags(['name' => $tags_name]);
                $tagModel->save(true, ['name']);
                $tagIds[] += $tagModel->id;
            }
        }
        
        return array_merge(array_values($tag_ids), $tagIds);
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
