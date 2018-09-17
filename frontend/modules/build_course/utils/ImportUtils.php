<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\build_course\utils;

use common\components\aliyuncs\Aliyun;
use common\models\vk\CourseActLog;
use common\models\vk\CourseNode;
use common\models\vk\CustomerWatermark;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
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
                $data['teacher.data'] = $this->checkTeacherExists(['name' => $data['teacher.name']], true);
            }
        }
        //如果是ajax则返回json格式的数据
        if($request_mode){
            Yii::$app->getResponse()->format = 'json';
            return $this->saveVideo($post);
        }else{
            return [
                'error_total' => 0,
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
                            $sex = trim(!empty($sheetdata[$row][$key]) ? $sheetdata[$row][$key] : '保密');
                            $sheetColumns['sex'] = $sexName[$sex];
                        }
                    }
                }
                //判断每一行是否存在空值，若存在则过滤
                if(!empty(array_filter($sheetdata[$row]))){
                    $dataProvider[] = $sheetColumns;
                }
            }
            //重置name、job_title，过滤字符串左后空格
            foreach($dataProvider as &$data){
                $data['name'] = trim($data['name']);
                $data['job_title'] = trim($data['job_title']);
            }
            
            return $this->batchSaveTeacher($dataProvider);
        }
    }

    /**
     * 导入框架信息
     * @param string $id    课程ID
     * @return type
     */
    public function importFrame($id)
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
            if(empty($dataProvider)){
                \Yii::$app->getSession()->setFlash('error', '未发现需要导入的数据!');
            } else {
                $node_names = ArrayHelper::getColumn($dataProvider, 'node.name');   //节点名称
                //节点整列为空或第一个为空时不能导入
                $has_first_node = empty($node_names) ? false : (empty($node_names['0']) ? false : true);
                if($has_first_node){
                    $video_ids = ArrayHelper::getColumn($dataProvider, 'video.id');     //视频ID
                    $is_error = [];
                    foreach ($video_ids as $key => $video_id) {
                        $is_error += [$key => Video::findOne(['id' => $video_id])];     //根据videoid查找数据
                    }
                    if(in_array('', $video_ids)){   //是否存在空值
                        \Yii::$app->getSession()->setFlash('error', '导入失败：video.id列不能存在空值!');
                    } elseif (in_array('', $is_error)) {
                        \Yii::$app->getSession()->setFlash('error', '导入失败：video.id列存在无效数据!');
                    } else {
                        return $this->saveCourseFrame($id, $dataProvider);
                    }
                } else {
                    \Yii::$app->getSession()->setFlash('error', '导入失败：node.name列第一个为空或全部为空!');
                }                
            }
        }
    }

    /**
     * 保存课程框架信息
     * @param string $id    课程ID
     * @param array $dataProvider   框架数据
     */
    protected function saveCourseFrame($id, $dataProvider)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $node_num = 0; $knowledge_num = 0;
            foreach ($dataProvider as $key => $data_val) {
                $video_model = Video::findOne(['id' => $data_val['video.id']]);     //视频模型
                $knowledge_id = md5(time() . rand(1, 99999999));
                //判断循环到第二次之后的node.name是否与前一次的node.name相同
                $is_true = $key == 0 ? true : ($data_val['node.name'] != $pre_node_name);
                if(!empty($data_val['node.name']) && $is_true){     //node.name不为空
                    $node_model = CourseNode::findOne(['course_id' => $id, 'name' => $data_val['node.name']]);  //节点模型
                    //node.name不能存在数据表中
                    if(empty($node_model)){
                        $node_id = md5(time() . rand(1, 99999999));
                        $course_node = [
                            'id' => $node_id,
                            'course_id' => $id,
                            'parent_id' => '',
                            'level' => 1,
                            'name' => $data_val['node.name'],
                            'des' => Html::encode($data_val['node.des']),
                            'is_del' => 0,
                            'sort_order' => $key,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        Yii::$app->db->createCommand()->insert(CourseNode::tableName(), $course_node)->execute(); //保存节点
                        $pre_node_id = $node_id;    //前一个node_id
                        $pre_node_name = $data_val['node.name'];    //作为前一个的节点名称
                        $node_num++;
                    } else {
                        $node_id = $node_model->id;
                        $pre_node_id = $node_model->id;
                        $pre_node_name = $node_model->name;
                    }
                } else {
                    $node_id = $pre_node_id;
                }
                $knowledge_model = Knowledge::findOne(['node_id' => $node_id, 'name' => $data_val['knowledge.name']]); //知识点模型
                //knowledge.name不能存在数据表中
                if(empty($knowledge_model)){
                    $knowledge = [
                        'id' => $knowledge_id,
                        'node_id' => $node_id,
                        'type' => 1,        //类型；1为视频
                        'name' => $data_val['knowledge.name'],
                        'des' => empty($data_val['des']) ?  $video_model->des : 
                                    Html::encode($data_val['knowledge.des']),
                        'data' => $video_model->duration,   //视频时长
                        'zan_count' => 0,
                        'favorite_count' => 0,
                        'is_del' => 0,
                        'has_resource' => 1,        //是否关联资源 1
                        'sort_order' => $key,
                        'created_by' => Yii::$app->user->id,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    Yii::$app->db->createCommand()->insert(Knowledge::tableName(), $knowledge)->execute();  //保存知识点
                    $knowledge_num++;
                    $knowledge_video = [
                        'knowledge_id' => $knowledge_id,
                        'video_id' => $data_val['video.id'],
                        'is_del' => 0,
                    ];
                    //关联知识点和视频
                    Yii::$app->db->createCommand()->insert(KnowledgeVideo::tableName(), $knowledge_video)->execute();
                }
            }
            if($node_num > 0 || $knowledge_num > 0){
                $this->saveCourseActLog($id, $node_num, $knowledge_num);    //保存导入记录
                Yii::$app->getSession()->setFlash('success','导入成功！');
            } else {
                Yii::$app->getSession()->setFlash('error','未发现需要导入的新数据！');
            }
            $trans->commit();  //提交事务
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','导入失败::'.$ex->getMessage());
        }
        
    }
    
    /**
     * 保存导入记录
     * @param string $id
     * @param integer $node_num
     * @param integer $knowledge_num
     */
    private function saveCourseActLog($id, $node_num, $knowledge_num)
    {
        //$actLog数组
        $actLog = [
            'action' => '导入',
            'title' => '导入课程目录',
            'content' => '共导入'.$node_num.'个节点，'.$knowledge_num.'个知识点',
            'created_by' => Yii::$app->user->id,
            'course_id' => $id, 
            'related_id' => '',
            'created_at' => time(),
            'updated_at' => time(),
        ];
        
        /** 添加$actLog数组到表里 */
        Yii::$app->db->createCommand()->insert(CourseActLog::tableName(), $actLog)->execute();
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
        $teacher_jobTitle = ArrayHelper::getColumn($dataProvider, 'job_title');     //老师职称
        
        //获取重复的老师信息
        $repeat_name = array_diff_assoc($teacher_name, array_unique($teacher_name));  //重复老师名
        $repeat_sex = array_diff_assoc($teacher_sex, array_unique($teacher_sex));     //重复老师性别
        $repeat_jobTitle = array_diff_assoc($teacher_jobTitle, array_unique($teacher_jobTitle));   //重复老师职称
        
        //查询已经存在的老师
        $teacher_result = $this->checkTeacherExists([
            'name' => array_unique($teacher_name), 
            'sex' => array_unique($teacher_sex),
        ]);
        //重组已经存在的数据格式
        foreach ($teacher_result as $value) {
            $key = $value['name'] . '_' . $value['sex'];
            $data_exist[$key] = $value;
        }
        //循环判断是否存在重复的数据和已经存在的数据
        foreach($dataProvider as $key => $data){
            $data_key = $data['name'] . '_' . $data['sex'];    //组装存在的key值
            //根据老师名、老师性别和老师职称判断
            if(isset($repeat_name[$key]) && isset($repeat_sex[$key]) && isset($repeat_jobTitle[$key])){
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
                    $fileName = $this->saveDrawing($data_val['coordinates'], $data_val['id'], 'upload/teacher/avatars/');
                    $data_val['avatar'] = $fileName . '?rand=' . rand(0, 9999);
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
    
    /**
     * 保存视频信息
     * @param array $post
     * @return json
     */
    protected function saveVideo($post)
    {
        $is_success = true;
        $dir_path = ArrayHelper::getValue($post, 'dir_path'); //目录路径
        $file_id = ArrayHelper::getValue($post, 'file_id'); //文件id
        $tags_name = ArrayHelper::getValue($post, 'tags_name'); //标签名
        $watermark_ids = implode(',', ArrayHelper::getValue($post, 'watermark_id', [])); //水印id
        //新建一个 video 模型
        $videoModel = new Video([
            'id' => md5(time() . rand(1, 99999999)),
            'teacher_id' => ArrayHelper::getValue($post, 'teacher_id'),
            'customer_id' => Yii::$app->user->identity->customer_id,
            'name' => ArrayHelper::getValue($post, 'name'),
            'is_publish' => 1,
            'created_by' => Yii::$app->user->id
        ]);
        //检查上传的视频文件是否已经被占用
        $userInfo = ActionUtils::getInstance()->getUploadVideoFileUserInfo($file_id);
        if($userInfo['results']){
            $is_success = false;
            $videoModel->id = $userInfo['data']['video_id'];
            $message = '视频已被使用。';
        }
        /* 如果is_success是true，则执行 */
        if($is_success){
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {
                /* 以 file_id 查询 Uploadfile 信息，再赋值给 video 模型 */
                $uploadfileModel = Uploadfile::findOne($file_id);
                $videoModel->duration = $uploadfileModel->duration;
                $videoModel->img = $uploadfileModel->thumb_path;
                $videoModel->is_link = $uploadfileModel->is_link;
                $videoModel->mts_watermark_ids = $this->checkCustomerWatermark($watermark_ids);
                $videoModel->user_cat_id = $this->checkVideoDirExists($dir_path);   //用户自定义目录id
                //如果 video 保存成功，则执行
                if($videoModel->save()){
                    $videoFile = new VideoFile([
                        'video_id' => $videoModel->id, 'is_source' => 1, 'file_id' => $file_id,
                    ]);
                    //如果 videoFile 模型 保存成功，则执行视频转码
                    if($videoFile->save()){
                        VideoAliyunAction::addVideoTranscode($videoModel->id);
                        VideoAliyunAction::addVideoSnapshot($videoModel->id);
                    }
                    //保存视频引用标签
                    $this->saveVideoTagRefs($videoModel->id, $tags_name);
                }else{
                    $is_success = false;
                }
                /* 如果is_success是true，则执行 */
                if($is_success){
                    $trans->commit();  //提交事务
                    $message = '保存成功。';
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                $is_success = false;
                $message = '保存失败::' . $ex->getMessage();
            }
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => ['id' => $videoModel->id, 'name' => $videoModel->name],
            'message' => $message
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
        Aliyun::getOss()->putObject($myFilePath . $myFileName, $imageContents, []); //写入文件到指定目录下
        
        return $myFilePath . $myFileName;
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
            'name' => $name, 'parent_id' => $parent_id, 'type' => 1,
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
     * 保存视频引用标签
     * @param string $video_id      视频id
     * @param string $video_tags    标签名
     */
    protected function saveVideoTagRefs($video_id, $video_tags)
    {
        $is_success = true;
        $tagRefs = [];
        $tags_ids = $this->checkTagsExists($video_tags);    //检查标签是否存在
        /* 设置引用标签次数 */
        foreach ($tags_ids as $id) {
            $tags = Tags::findOne($id);
            $tags->ref_count = $tags->ref_count + 1;
            if(!$tags->save(true, ['ref_count'])){
                $is_success = false;
                break;
            }
            //组装引用的视频标签
            $tagRefs[] = ['object_id' => $video_id, 'tag_id' => $id, 'type' => 2];
        }
        //保存标签引用次数成功后添加视频引用标签
        if($is_success){
            Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(), 
                isset($tagRefs[0]) ? array_keys($tagRefs[0]) : [], $tagRefs)->execute();
        }
    }
    
    /**
     * 检查是使用集团下默认选中的水印，还是用户自选的水印
     * @param string $video_watermark   视频水印
     * @return string  返回集团下默认选中的水印 或 用户自选的水印
     */
    protected function checkCustomerWatermark($video_watermark)
    {
        if($video_watermark == null){
            //查询客户下的水印图
            $watermark = (new Query())->from(['Watermark' => CustomerWatermark::tableName()]);
            $watermark->select(['Watermark.id']);
            $watermark->where([
                'Watermark.customer_id' => \Yii::$app->user->identity->customer_id,
                'Watermark.is_del' => 0, 'Watermark.is_selected' => 1
            ]);
            return implode(',', $watermark->all());
        }
        
        return $video_watermark;
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
        $userCategory->where(['UserCategory.name' => $dirs, 'UserCategory.type' => 1]);
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
