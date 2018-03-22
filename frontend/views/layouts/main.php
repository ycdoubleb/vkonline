<?php

use common\widgets\Alert;
use frontend\assets\AppAsset;
use kartik\widgets\AlertBlock;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Breadcrumbs;

/* @var $this View */
/* @var $content string */


AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    
    $leftMenuItems = [];
    $rightMenuItems = [];
    
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    
    if (Yii::$app->user->isGuest) {
        //右边导航
        $rightMenuItems = [
            [
                'label' => Yii::t('app', 'Signup'), 'url' => ['/site/signup'],
            ],
            [
               'label' => Yii::t('app', 'Login'), 'url' => ['/site/login']
            ]
        ];
    } else {
        //左边导航
        $leftMenuItems = [    
            [
                'label' => Yii::t('app', 'Home'), 'url' => ['/site/index'],
                //'options' => ['class' => 'pull-left'],
            ],
            [
                'label' => Yii::t('app', 'Course'), 'url' => ['/course/default']
            ],
            [
                'label' => Yii::t('app', 'Video'), 'url' => ['/video/default']
            ],
            [
                'label' => Yii::t('app', '{Study}{Center}', ['Study' => Yii::t('app', 'Study'),'Center' => Yii::t('app', 'Center'),]), 
                'url' => ['/study_center/default']
            ],
            [
                'label' => Yii::t('app', 'Square'), 'url' => ['/site/index']
            ],
        ];
        //右边导航
        $rightMenuItems = [
            [
                'label' => Yii::t('app', '{Build}{Center}',['Build' => Yii::t('app', 'Build Course'),'Center' => Yii::t('app', 'Center'),]), 
                'url' => ['/build_course/default']
            ],
            [
                'label' => Yii::t('app', '{Help}{Center}',['Help' => Yii::t('app', 'Help'),'Center' => Yii::t('app', 'Center'),]), 
                'url' => ['/site/index']
            ],
            [
                'label' => Yii::t('app', '{Admin}{Center}',['Admin' => Yii::t('app', 'Admin'),'Center' => Yii::t('app', 'Center')]), 
                'url' => ['/admin_center/default']
            ],
//            ['label' => 'About', 'url' => ['/site/about']],
//            ['label' => 'Contact', 'url' => ['/site/contact']],
        ];
        //右边退出导航
        $rightMenuItems[] = [
            'label' => Html::img([Yii::$app->user->identity->avatar], ['width' => 28, 'height' => 28, 'class' => 'img-circle','style' => 'margin-right: 5px;']),
            'url' => ['/user/default/index'],
            'options' => ['class' => 'logout'],
            'items' => [
//                ['label' => Html::a(Html::img(['/resources/avatars/default.jpg']/*[Yii::$app->user->identity->avatar]*/, [
//                    'class' => 'img-circle avatars-circle', 
//                ]), Url::to(['/user/default/index'], true))],
//                ['label' => Html::a(Yii::$app->user->identity->real_name, Url::to(['/user/default/index'], true)), 
//                    'options' => [
//                        'class' => 'user-name', 
//                    ]
//                ],
//                ['label' => (Yii::$app->user->identity->isRoleStudent()?'学习课程数':'观摩课程数').'<em>'.
//                        (!empty($studyLogs['cour_num']) ? $studyLogs['cour_num'] : 0).
//                    '</em>'.'门',
//                    'options' => [
//                        'class' => 'study-course', 
//                    ]
//                ],
//                ['label' => '学校'.'<span>'.'广远实验小学'.'</span>',
//                    'options' => [
//                        'class' => 'user-school', 
//                    ]
//                ],
//                ['label' => 
//                    //如果是学生角色显示年级，否则显示职称
//                    (Yii::$app->user->identity->isRoleStudent() ?
//                        Yii::t('app', 'Grade').'<span>'.Yii::$app->user->identity->profile->getGrade().'</span>' :
//                        Yii::t('app', 'Job Title').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp'
//                    ),
//                    'options' => [
//                        'class' => 'user-identity', 
//                    ]
//                ],
//                ['label' => "<i class=\"fa fa-clock-o\"></i>".
//                    (!empty($studyLogs['cour_name']) ? Html::a("《{$studyLogs['cour_name']}》",
//                        Url::to(['/study/college/view', 'id' => $studyLogs['course_id']]),['title' => '最近观看：'.date('Y-m-d H:i',$studyLogs['upd_at'])]).
//                    Html::a("<span class=\"keep-look\">"."<i class=\"fa fa-play-circle-o\"></i>".
//                        (Yii::$app->user->identity->isRoleStudent()?'继续学习':'继续观摩').
//                    "</span>",Url::to(['/study/college/view', 'id' => $studyLogs['course_id']])):"暂无观看记录"), 
//                    'options' => [
//                        'class' => 'last-study',
//                    ]
//                ],
                [
                    'label' => '<i class="fa fa-sign-out"></i>'.Yii::t('app', 'Logout'), 
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post','class' => 'logout']
                ],
            ],
        ];
                
//        '<li>'
//        . Html::beginForm(['/site/logout'], 'post')
//        . Html::submitButton(
//              Html::img(['/resources/avatars/default.jpg'], ['width' => 28, 'height' => 28, 'class' => 'img-circle', 'style' => 'margin-right: 5px;']),
//
//            'Logout (' . Yii::$app->user->identity->username . ')',
//            ['class' => 'btn btn-link logout']
//        )
//        . Html::endForm()
//        . '</li>';
    }
    
    $moduleId = Yii::$app->controller->module->id;   //模块ID
    if($moduleId == 'app-frontend'){
        //站点经过首页或登录，直接获取当前路由
        $route = Yii::$app->controller->getRoute();
    }else{
        $urls = [];
        $vals = [];
        $leftMenuUrls = ArrayHelper::getColumn($leftMenuItems, 'url');
        $rightMenuUrls = ArrayHelper::getColumn($rightMenuItems, 'url');
        $menuUrls = array_merge($leftMenuUrls, $rightMenuUrls);
        foreach ($menuUrls as $url){
            $urls[] = array_filter(explode('/', $url[0]));
        }
        foreach($urls as $val){
            $vals[$val[1]] = implode('/', $val);
        }
        try{
            $route = substr($vals[$moduleId], 0);
        } catch (Exception $ex) {
             $route = Yii::$app->controller->getRoute();    
        }
    }
    
    //左边
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left'],
        'encodeLabels' => false,
        'items' => $leftMenuItems,
        'activateParents' => true,
        'route' => $route,
    ]);
    //右边
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'encodeLabels' => false,
        'items' => $rightMenuItems,
        'activateParents' => true,
        'route' => $route,
    ]);
    
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= AlertBlock::widget([
            'useSessionFlash' => TRUE,
            'type' => AlertBlock::TYPE_GROWL,
            'delay' => 0
        ]);?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
