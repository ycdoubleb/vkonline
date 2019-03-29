<?php

use common\models\AdminUserr;
use common\widgets\Menu;

/* @var $user AdminUserr */
?>
<aside class="main-sidebar">
    <section class="sidebar">

        <!-- Sidebar user panel -->
        <?php if(!Yii::$app->user->isGuest): ?>
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $user->avatar; ?>" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= $user->nickname ?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
        <?php 
            $menuItems = [['label' => 'Menu Yii2', 'options' => ['class' => 'header']]];
            if(Yii::$app->user->isGuest){
                $menuItems []= ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest];
            }else{
                $menuItems = array_merge($menuItems, [
                    ['label' => '清除缓存', 'icon' => 'eraser', 'url' => ['/system_admin/cache']],
                    [
                        'label' => '系统',
                        'icon' => 'bars',
                        'url' => '#',
                        'items' => [
                            ['label' => '配置管理', 'icon' => 'circle-o', 'url' => ['/system_admin/config'],],
                            ['label' => '文件管理', 'icon' => 'circle-o', 'url' => ['/system_admin/uploadfile'],],
                            ['label' => '日常任务', 'icon' => 'circle-o', 'url' => ['/system_admin/task-log'],],
                            ['label' => '数据库备份', 'icon' => 'database', 'url' => ['/system_admin/db-backup']],
                            ['label' => 'redis缓存管理', 'icon' => 'circle-o', 'url' => ['/rediscache_admin/acl/index']],
                        ],
                    ],
                    [
                        'label' => '权限与组织管理',
                        'icon' => 'bars',
                        'url' => '#',
                        'items' => [
                            ['label' => '用户列表', 'icon' => 'circle-o', 'url' => ['/user_admin'],],
                            ['label' => '用户角色', 'icon' => 'circle-o', 'url' => ['/rbac/user-role'],],
                            ['label' => '角色列表', 'icon' => 'circle-o', 'url' => ['/rbac/role'],],
                            ['label' => '权限列表', 'icon' => 'circle-o', 'url' => ['/rbac/permission'],],
                            ['label' => '路由列表', 'icon' => 'circle-o', 'url' => ['/rbac/route'],],
                            ['label' => '分组列表', 'icon' => 'circle-o', 'url' => ['/rbac/auth-group'],],
                        ],
                    ],
                    [
                        'label' => '前台管理',
                        'icon' => 'bars',
                        'url' => '#',
                        'items' => [
                            ['label' => '授权管理', 'icon' => 'circle-o', 'url' => ['/frontend_admin/brand-authorize']],
                            ['label' => '概况', 'icon' => 'circle-o', 'url' => ['/frontend_admin/default']],
                            ['label' => '客户', 'icon' => 'circle-o', 'url' => ['/frontend_admin/customer']],
                            ['label' => '用户', 'icon' => 'circle-o', 'url' => ['/frontend_admin/user']],
                            ['label' => '课程', 'icon' => 'circle-o', 'url' => ['/frontend_admin/course']],
                            ['label' => '视频', 'icon' => 'circle-o', 'url' => ['/frontend_admin/video']],
                            ['label' => '宣传栏', 'icon' => 'circle-o', 'url' => ['/frontend_admin/banner']],
                            ['label' => '课程分类', 'icon' => 'circle-o', 'url' => ['/frontend_admin/category']],
                            ['label' => '公用目录', 'icon' => 'circle-o', 'url' => ['/frontend_admin/user-category']],
                            ['label' => '老师列表', 'icon' => 'circle-o', 'url' => ['/frontend_admin/teacher']],
                            ['label' => '认证申请', 'icon' => 'circle-o', 'url' => ['/frontend_admin/certificate']],
                            ['label' => '套餐管理', 'icon' => 'circle-o', 'url' => ['/frontend_admin/good']],
                            ['label' => '用户反馈', 'icon' => 'circle-o', 'url' => ['/frontend_admin/feedback']],
                        ],
                    ],
                    [
                        'label' => '帮助中心',
                        'icon' => 'bars',
                        'url' => '#',
                        'items' => [
                            ['label' => '文章分类列表', 'icon' => 'circle-o', 'url' => ['/helpcenter_admin/category']],
                            ['label' => '文章列表', 'icon' => 'circle-o', 'url' => ['/helpcenter_admin/post']],
                        ],
                    ],
                ]);
            }
        ?>
        <?= Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => $menuItems,
            ]
        ) ?>

    </section>

</aside>
