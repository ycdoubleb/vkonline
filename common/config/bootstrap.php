<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@apiend', dirname(dirname(__DIR__)) . '/apiend');
Yii::setAlias('@dailylessonend', dirname(dirname(__DIR__)) . '/dailylessonend');

defined('WEB_ROOT') or define('WEB_ROOT',defined('YII_ENV_TT') ? 'http://tt.studying8.com' :'http://www.studying8.com');