<?php

namespace frontend\modules\build_course\controllers;

use common\components\aliyuncs\Aliyun;
use common\models\api\ApiResponse;
use common\models\User;
use common\models\vk\Audio;
use common\models\vk\CustomerWatermark;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use Exception;
use frontend\modules\build_course\utils\VideoAliyunAction;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * 批量导入视频
 *
 * @author Administrator
 */
class AudioImportController extends Controller{
    public $layout = '@frontend/views/layouts/main_no_nav';
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
        
        $audios = $isImport ? $this->getSpreadsheet('importfile') : [];
        
        return $this->render('index',[
            'isImport' => $isImport,                                                //导入中，选择文件表上传后
            'user_cat_id' => ArrayHelper::getValue($params, 'user_cat_id', 0),      //存放目录
            'audios' => $audios,                                                    //excel表的音频信息
        ]);
    }
    
    /**
     * 添加音频
     * post = 
     * [
     *  Video:[name,des,],
     *  user_cat_id : "xx",
     *  audio_tags:"1,2,3,4,5",
     *  aduio_file:"xxxx"
     * ]
     */
    public function actionAddAudio(){
        \Yii::$app->response->format = 'json';
        $post = Yii::$app->request->post();
        $file_id = ArrayHelper::getValue($post, 'audio_file');
        
        $uploadfile = Uploadfile::findOne($file_id);
        if(!$uploadfile){
            return [
                'success' => true,
                'data' => new ApiResponse('FILE_NOT_FOUND', '音频文件不能为空！'),
            ];
        }
        /* 配置特定属性 */
        $audio = new Audio([
            'id'            => md5(time() . rand(1, 99999999)),
            'customer_id'   => Yii::$app->user->identity->customer_id, 
            'user_cat_id'   => (integer)ArrayHelper::getValue($post, 'user_cat_id' , 0), 
            'file_id'       => $uploadfile->id,
            'duration'      => $uploadfile->duration,
            'created_by'    => Yii::$app->user->id,
            'is_publish'    => 1,
        ]);
        try{
            $audio->loadDefaultValues();
            if($audio->load($post) && $audio->validate()){
                $trans = Yii::$app->db->beginTransaction();
                //保存Audio
                if($audio->save()){
                    //新建关键字
                    $tags = Tags::saveTags(ArrayHelper::getValue($post, 'audio_tags' , []));
                    //关联关键字
                    $this->saveAudioTags($audio->id, $tags);
                    $trans->commit();
                    return [
                        'success' => true,
                        'data' => new ApiResponse(ApiResponse::CODE_COMMON_OK, null , $audio->toArray()),
                    ];
                }else{
                    return [
                        'success' => true,
                        'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $audio->getErrorSummary(true)),
                    ];
                }
            }else{
                return [
                    'success' => true,
                    'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $audio->getErrorSummary(true)),
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
     * 更新视频
     */
    public function actionUpdateVideo(){
        
    }
    
    /**
     * 获取音频信息
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
     * 保存标签
     * @param string        $audio_id      音频ID
     * @param array<Tags>   $tags         标签
     */
    private function saveAudioTags($audio_id,$tags){
        //准备数据
        $rows = [];
        /* @var $tag Tags */
        foreach ($tags as $tag) {
            $rows[] = [$audio_id, $tag->id, 3];
        }
        //保存关联
        \Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(), ['object_id', 'tag_id', 'type'], $rows)->execute();
        //累加引用次数
        Tags::updateAllCounters(['ref_count' => 1], ['id' => ArrayHelper::getColumn($tags, 'id')]);
    }
}
