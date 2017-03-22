<?php

// comment out the following two lines when deployed to production
if (isset($_SERVER['YII_ENV'])) define('YII_ENV', $_SERVER['YII_ENV']);
defined('YII_ENV') or define('YII_ENV', 'dev');
if (YII_ENV === 'dev') define('YII_ENV_DEV', true);

if (defined('YII_ENV_DEV')) defined('YII_DEBUG') or define('YII_DEBUG', true);

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
