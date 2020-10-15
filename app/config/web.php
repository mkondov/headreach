<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'on beforeRequest' => function ($event) {
        if(!Yii::$app->request->isSecureConnection){
            // add some filter/exemptions if needed ..
            $url = Yii::$app->request->getAbsoluteUrl();
            $url = str_replace('http:', 'https://', $url);
            Yii::$app->getResponse()->redirect($url);
            Yii::$app->end();
        }
    },
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'xxxxxx',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
        	'flushInterval' => 1,
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                	'exportInterval' => 1,
                    'levels' => ['error', 'warning', 'trace'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'db_wp' => require(__DIR__ . '/db_wp.php'),

        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Disable index.php
            // 'urlFormat' => 'path',
            'showScriptName' => false,
            // Disable r= routes
            'enablePrettyUrl' => true,
            'rules' => array(
                'MyFirstYii/post/<view>' => 'MyFirstYii/post', 
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/id/<id:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<id:\w+>/page/<page:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/<type:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>/type/<type:\w+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
            // 'rules' => array(
            //     '<controller:\w+>/<id:\d+>' => '<controller>/view',
            //     '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            //     '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            // ),
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];

// if (YII_ENV_DEV) {
//     // configuration adjustments for 'dev' environment
//     $config['bootstrap'][] = 'debug';
//     $config['modules']['debug'] = [
//         'class' => 'yii\debug\Module',
//     ];
//     $config['modules']['debug']['allowedIPs'] = ['*'];

//     $config['bootstrap'][] = 'gii';
//     $config['modules']['gii'] = [
//         'class' => 'yii\gii\Module',
//     ];
// }

return $config;