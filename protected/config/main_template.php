<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
date_default_timezone_set('Europe/Moscow');

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Admintrix',
    //'defaultController' => 'user/default',
    'language' => 'en',
	// preloading 'log' component
	'preload'=>array('log','booster'),
    'theme' => explode('.',$_SERVER['SERVER_NAME'])[0],
    'aliases'=>array(
		'bootstrap'=>realpath(__DIR__.'/../extensions/yiistrap'),
		'booster'=>realpath(__DIR__.'/../extensions/booster'),
	),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.helpers.Tools',
		'application.modules.user.*',
		'application.modules.user.models.*',
		'application.modules.user.components.*',
		'application.modules.rights.*',
		'application.modules.rights.models.*',
		'application.modules.rights.components.*',
		'application.modules.project.*',
		'application.modules.project.models.*',
		'application.modules.project.controllers.*',
		'application.behaviors.*',
		//'application.extensions.yiifilemanager.*',
		//'application.extensions.yiifilemanagerfilepicker.*',
		//'application.extensions.helpers.EDownloadHelper',
		'application.extensions.yiichat.*',
		'ext.YiiMailer.YiiMailer',
		'bootstrap.helpers.TbHtml',
		'ext.juidatetimepicker.EJuiDateTimePicker',
    ),
	'modules'=>array(
        /*'SimplePaypal' => array(
            'components' => array(
                'paypalManager' => array(
                    'class' => 'SimplePaypal.components.Paypal',
                ),
            ),
        ),*/
        'user' => array(
        // названия таблиц взяты по умолчанию, их можно изменить
            'tableUsers' => 'Users',
            'tableProfiles' => 'Profiles',
            'tableProfileFields' => 'ProfilesFields',
        ),
        //'organization',
        'rights',
        'project',
       // uncomment the following to enable the Gii tool
		/*'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'sunrise',
            'generatorPaths' => array('booster.gii'),
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			//'ipFilters'=>array('127.0.0.1','::1','192.168.0.*','217.175.0.36'),
		),*/
	),
	// application components
	'components'=>array(
        'clientScript' => array
        (
            'class' => 'CClientScript',
            'scriptMap' => array
            (
                'jquery.js' => '/js/jquery-1.11.2.js',
            ),
        ),
        'user'=>array(
            'class' => 'RWebUser',
            'loginUrl'=>array('user/login'),
            'allowAutoLogin'=>true,
        ),
        'authManager'=>array(
            'class'=>'RDbAuthManager',
            'defaultRoles' => array('Guest'),
            'connectionID'=>'db'
        ),
        'bootstrap' => array(
            'class' => 'bootstrap.components.TbApi',
        ),
        'booster' => array(
            'class' => 'application.extensions.booster.components.Booster',
            'minify' => false,
        ),
/*		
        'fileman' => array(
                        'class'=>'application.extensions.yiifilemanager.YiiDiskFileManager',
                        'storage_path' => 'uploads',
        ),
*/		
        'jsonRequest' => array(
            'class' => 'JsonHttpRequest'
        ),
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
            'showScriptName'=>false,
		),

        //dev server
        'db'=>include 'db.php',
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CWebLogRoute',
                    'categories' => 'application',
                    'levels'=>'error, warning, trace, profile, info',
                    'showInFireBug' => true
                ),
            ),
        ),
		'cdr' => [
            'class' => 'application.components.TelphinCdrComponent',
            //'app_id' => '********************************',
            //'app_secret' => '********************************',
            'extension' => 100,
            'count' => 2000,
        ],
	),
    'timeZone' => 'Europe/Moscow',
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'rpswd' => 'rpswd',
		// this is used in contact page
		//'adminEmail'=>'info@dipstart.ru',
		//'supportEmail'=>'ekomixds@mail.ru',
	),
);
