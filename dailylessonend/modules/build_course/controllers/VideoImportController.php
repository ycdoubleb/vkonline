<?php

namespace dailylessonend\modules\build_course\controllers;

use common\components\aliyuncs\Aliyun;
use common\models\api\ApiResponse;
use common\models\User;
use common\models\vk\CustomerWatermark;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use dailylessonend\modules\build_course\utils\VideoAliyunAction;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * 批量导入素材
 *
 * @author Administrator
 */
class VideoImportController extends Controller{
    public $layout = '@dailylessonend/views/layouts/main_no_nav';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add-video' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ]
        ];
    }
    
    
    /**
     * 进入批量导入界面
     */
    public function actionIndex(){
        $params = \Yii::$app->request->queryParams;
        $isImport = Yii::$app->request->isPost;
        
        $videos = $isImport ? $this->getSpreadsheet('importfile') : [];
        $typeMap = [];
        $teachers = $isImport ? $this->getTeachers(ArrayHelper::getColumn($videos, 'teacher.name')) : [];
        foreach (Video::$typeMap as $key => $value){
            $typeMap[] = [ 
                'id' => $key,
                'text' => $value 
            ];
        }
        
        return $this->render('index',[
            'isImport' => $isImport,                                                //导入中，选择文件表上传后
            'user_cat_id' => ArrayHelper::getValue($params, 'user_cat_id', 0),      //存放目录
            'mts_watermark_ids'   => ArrayHelper::getValue($params, 'mts_watermark_ids' , ''), 
            'watermarks' => json_encode($this->getCustomerWatermark()),             //品牌水印数据
            'videos' => $videos,                                                    //excel表的素材信息
            'typeMap' => $typeMap,                                           //素材类型
            'teachers' => $teachers,                                                //表中所有涉及的老师
        ]);
    }
    
    /**
     * 添加素材
     * post = 
     * [
     *  Video:[name,teacher_id,des,],
     *  user_cat_id : "xx",
     *  mts_watermark_ids : "xxx,xxx",
     *  video_tags:"1,2,3,4,5",
     *  video_file:"xxxx"
     * ]
     */
    public function actionAddVideo(){
        \Yii::$app->response->format = 'json';
        $post = Yii::$app->request->post();
        $type = ArrayHelper::getValue($post, 'Video.type');
        $file_id = ArrayHelper::getValue($post, 'video_file');
        
        /* 检查文件使用情况 */
        $checkFileResult = $this->checkFileHasUse($file_id);
        //已经使用过
        if($checkFileResult && $type == Video::TYPE_VIDEO){
            return [
                'success' => false,
                'data' => new ApiResponse('FILE_REPEAT', '文件重复使用', $checkFileResult),
            ];
        }
        $uploadfile = Uploadfile::findOne($file_id);
        if(!$uploadfile){
            return [
                'success' => true,
                'data' => new ApiResponse('FILE_NOT_FOUND', '素材文件不能为空！'),
            ];
        }
        /* 配置特定属性 */
        $video = new Video([
            'id'            => md5(time() . rand(1, 99999999)),
            'customer_id'   => Yii::$app->user->identity->customer_id, 
            'mts_watermark_ids'   => ArrayHelper::getValue($post, 'mts_watermark_ids' , ''), 
            'user_cat_id'   => (integer)ArrayHelper::getValue($post, 'user_cat_id' , 0), 
            'file_id'       =>  $file_id,
            'duration'      => $uploadfile->duration,
            'img'           => $uploadfile->thumb_path,
            'is_link'       => $uploadfile->is_link,
            'created_by'    => Yii::$app->user->id,
            'is_publish'    => 1,
        ]);
        try{
            $video->loadDefaultValues();
            if($video->load($post) && $video->validate()){
                $trans = Yii::$app->db->beginTransaction();
                if($video->save()){
                    //添加转码、截图
                    VideoAliyunAction::addVideoTranscode($video);
                    VideoAliyunAction::addVideoSnapshot($video);
                    //新建关键字
                    $tags = Tags::saveTags(ArrayHelper::getValue($post, 'video_tags' , []));
                    //关联关键字
                    $this->saveVideoTags($video->id, $tags);
                    $trans->commit();
                    return [
                        'success' => true,
                        'data' => new ApiResponse(ApiResponse::CODE_COMMON_OK, null , $video->toArray()),
                    ];
                }else{
                    return [
                        'success' => true,
                        'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $video->getErrorSummary(true)),
                    ];
                }
            }else{
                return [
                    'success' => true,
                    'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $video->getErrorSummary(true)),
                ];
            }
        } catch (Exception $ex) {
            $trans->rollBack();
            return [
                'success' => false,
                'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, $ex->getMessage(), $ex->getTraceAsString()),
            ];
        }
    }
    /**
     * 更新素材
     */
    public function actionUpdateVideo(){
        
    }
    
    /**
     * 获取素材信息
     * @param type $name    filename
     */
    private function getSpreadsheet($name){
        $dataProvider = [];
        $upload = UploadedFile::getInstanceByName($name);
        if($upload != null){
            $spreadsheet = IOFactory::load($upload->tempName); // 载入excel文件
            $sheet = $spreadsheet->getActiveSheet();    // 读取第一個工作表 
            $sheetdata = $sheet->toArray(null, true, true, true);   //转换为数组
            $sheetColumns = [];
            //获取组装的工作表数据
            for ($row = 3; $row <= count($sheetdata); $row++) {
                //组装对应数组值
                foreach ($sheetdata[2] as $key => $value) {
                    if(!empty($value)){ //值非空
                        $sheetColumns[$value] = trim($sheetdata[$row][$key]);
                    }
                }
                //判断每一行是否存在空值，若存在则过滤
                if(!empty(array_filter($sheetdata[$row]))){
                    $dataProvider[] = $sheetColumns;
                }
            }
        }
        return $dataProvider;
    }
    
    /**
     * 查找（自己或者已认证）所有老师
     * @param type $names
     * @return array 
     */
    private function getTeachers($names){
        $teachers = Teacher::find()
                ->select(['id', 'avatar', 'name', 'name as text', 'sex', 'job_title', 'is_certificate'])    //text 在select过滤使用
                ->where(['is_del' => 0])
                ->andWhere(['or', ['created_by' => \Yii::$app->user->id], ['name' => $names,'is_certificate' => 1]])
                ->orderBy(['is_certificate' => SORT_DESC , 'name' => SORT_ASC])
                ->asArray()
                ->all();
        //头像路径换成Aliyun路径
        foreach ($teachers as &$teacher) {
            $teacher['avatar'] = Aliyun::absolutePath($teacher['avatar']);
        }
        unset($teacher);
        return $teachers;
    }
    
    /**
     * 获取客户下已启用的所有水印图
     * @param integer|array $cw_id      水印图id
     * @return array
     */
    protected function getCustomerWatermark($cw_id = null)
    {
        //查询客户下的水印图
        $query = (new Query())->select([
            'Watermark.id', 'Watermark.width', 'Watermark.height', 
            'Watermark.dx AS shifting_X', 'Watermark.dy AS shifting_Y', 
            'Watermark.refer_pos', 'Watermark.is_selected', 'Uploadfile.oss_key'
        ])->from(['Watermark' => CustomerWatermark::tableName()]);
        //关联实体文件
        $query->leftJoin(['Uploadfile' => Uploadfile::tableName()], '(Uploadfile.id = Watermark.file_id AND Uploadfile.is_del = 0)');
        //条件
        $query->where([
            'Watermark.customer_id' => Yii::$app->user->identity->customer_id,
            'Watermark.is_del' => 0,
        ]);
        $query->andFilterWhere(['Watermark.id' => $cw_id]);
        //查询结果
        $watermarks = $query->all();
        //重置is_selected、path属性值
        foreach ($watermarks as $id => &$item) {
            $item['is_selected'] = $item['is_selected'] ? true : false;
            $item['path'] = Aliyun::absolutePath(!empty($item['oss_key'])? $item['oss_key'] : 'static/imgs/notfound.png' );
        }
        return ArrayHelper::index($watermarks, 'id');
    }
    
    /**
     * 检查实体文件是否已被使用过，
     * @param string $file_id
     * @return array 
     */
    private function checkFileHasUse($file_id){
        $result = (new Query())->select([
                            'Video.id as file_id',
                            'Video.id video_id', 'Video.name video_name', "FROM_UNIXTIME(Video.created_at,'%Y-%m-%d %H:%i') created_at",
                            'User.id user_id', 'User.nickname user_name',
                            'Uploadfile.name file_name',
                        ])
                        ->from(['Video' => Video::tableName()])
                        ->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by')
                        ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Video.file_id')
                        ->where([
                            'Video.file_id' => $file_id,
                            'Video.is_del' => 0,
                        ])->one();
      
        return $result;
    }
    
    /**
     * 保存标签
     * @param string        $video_id      素材ID
     * @param array<Tags>   $tags         标签
     */
    private function saveVideoTags($video_id,$tags){
        //准备数据
        $rows = [];
        /* @var $tag Tags */
        foreach ($tags as $tag) {
            $rows[] = [$video_id, $tag->id, 2];
        }
        //保存关联
        \Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(), ['object_id', 'tag_id', 'type'], $rows)->execute();
        //累加引用次数
        Tags::updateAllCounters(['ref_count' => 1], ['id' => ArrayHelper::getColumn($tags, 'id')]);
    }
}
