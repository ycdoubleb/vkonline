<?php

namespace dailylessonend\modules\build_course\controllers;

use common\models\api\ApiResponse;
use common\models\vk\Document;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\modules\webuploader\models\Uploadfile;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * 批量导入文档
 *
 * @author Administrator
 */
class DocumentImportController extends Controller{
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
                    'add-document' => ['POST'],
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
        
        $documents = $isImport ? $this->getSpreadsheet('importfile') : [];
        
        return $this->render('index',[
            'isImport' => $isImport,                                                //导入中，选择文件表上传后
            'user_cat_id' => ArrayHelper::getValue($params, 'user_cat_id', 0),      //存放目录
            'documents' => $documents,                                                    //excel表的文档信息
        ]);
    }
    
    /**
     * 添加文档
     * post = 
     * [
     *  Document:[name,des,],
     *  user_cat_id : "xx",
     *  document_tags:"1,2,3,4,5",
     *  aduio_file:"xxxx"
     * ]
     */
    public function actionAddDocument(){
        \Yii::$app->response->format = 'json';
        $post = Yii::$app->request->post();
        $file_id = ArrayHelper::getValue($post, 'document_file');
        
        $uploadfile = Uploadfile::findOne($file_id);
        if(!$uploadfile){
            return [
                'success' => true,
                'data' => new ApiResponse('FILE_NOT_FOUND', '文档文件不能为空！'),
            ];
        }
        /* 配置特定属性 */
        $document = new Document([
            'id'            => md5(time() . rand(1, 99999999)),
            'customer_id'   => Yii::$app->user->identity->customer_id, 
            'user_cat_id'   => (integer)ArrayHelper::getValue($post, 'user_cat_id' , 0), 
            'file_id'       => $uploadfile->id,
            'duration'      => $uploadfile->duration,
            'created_by'    => Yii::$app->user->id,
            'is_publish'    => 1,
        ]);
        try{
            $document->loadDefaultValues();
            if($document->load($post) && $document->validate()){
                $trans = Yii::$app->db->beginTransaction();
                //保存Document
                if($document->save()){
                    //新建关键字
                    $tags = Tags::saveTags(ArrayHelper::getValue($post, 'document_tags' , []));
                    //关联关键字
                    $this->saveDocumentTags($document->id, $tags);
                    $trans->commit();
                    return [
                        'success' => true,
                        'data' => new ApiResponse(ApiResponse::CODE_COMMON_OK, null , $document->toArray()),
                    ];
                }else{
                    return [
                        'success' => true,
                        'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $document->getErrorSummary(true)),
                    ];
                }
            }else{
                return [
                    'success' => true,
                    'data' => new ApiResponse(ApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $document->getErrorSummary(true)),
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
     * 更新文档
     */
    public function actionUpdateDocument(){
        
    }
    
    /**
     * 获取文档信息
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
     * @param string        $document_id      文档ID
     * @param array<Tags>   $tags         标签
     */
    private function saveDocumentTags($document_id,$tags){
        //准备数据
        $rows = [];
        /* @var $tag Tags */
        foreach ($tags as $tag) {
            $rows[] = [$document_id, $tag->id, 4];
        }
        //保存关联
        \Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(), ['object_id', 'tag_id', 'type'], $rows)->execute();
        //累加引用次数
        Tags::updateAllCounters(['ref_count' => 1], ['id' => ArrayHelper::getColumn($tags, 'id')]);
    }
}
