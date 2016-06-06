<?php

class ChatController extends Controller {

	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/main';
	/**
	 * @return array action filters
	 */
    protected $_request;
    protected $_response;
    protected function _prepairJson() {
        $this->_request = Yii::app()->jsonRequest;
        $this->_response = new JsonHttpResponse();
    }

	/*public function filters()
	{
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete, ApiRenameFile', // we only allow deletion via POST request
        );
	}*/

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	/*public function accessRules()
	{
		return array(
            array('allow', 
                'actions' => array ('ApiRenameFile'),
                'expression' => array('ChatController', 'allowOnlyOwner'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index','upload'),
                'users' => array('@'),
            ),
			array('allow',  // allow all users
				'actions'=>array('view'),
				'expression' => array('ChatController', 'allowAuthorsAndGuests'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}*/
    public static function allowOnlyOwner(){
        if(User::model()->isManager()){
            return true;
        }
        else{
            $zakaz = Zakaz::model()->resetScope()->findByPk($_GET["orderId"]);
            if(User::model()->isCustomer())
                return ($zakaz->user->id === Yii::app()->user->id);
            if(User::model()->isAuthor())
                return (($zakaz->executor == 0) || ($zakaz->executor === Yii::app()->user->id));
        }
    }
	/*public static function allowAuthorsAndGuests(){
		if(User::model()->isAuthor() || Yii::app()->user->isGuest) {
			return true;
		} else {
			return false;
		}
	}*/

	/**
	 *  Вывод и добавление сообщений
	 */
    public function actionIndex($orderId, $role = null) {
    	$isCorrector = $role == 'Corrector' ? 1 : 0;
		$isGuest = Yii::app()->user->isGuest;
		if ($isGuest) {
			$url = 'http://'.$_SERVER['SERVER_NAME'].'/user/login';
			$this->redirect($url);
		}
		
		Yii::app()->session['project_id'] = $orderId;
		$order = Zakaz::model()->resetScope()->findByPk($orderId);
		
        if (Yii::app()->request->isAjaxRequest) {
            if (Yii::app()->request->getPost('ProjectMessages')) {

				$id = (int)$_POST['ProjectMessages']['id'];
				if ($id>0)	{ // редактирование сообщения
					$model = ProjectMessages::model()->findByPk($id);
				} else {	  // новое сообщение
					$model = new ProjectMessages;
					$model->sender = Yii::app()->user->id;
					$model->moderated = 0;
					$model->order = $orderId;
				};
				$post	= $_POST['ProjectMessages']['message'];
				$post	= str_replace("\x0D\x0A",'<br>',$post);
				$post	= str_replace("\x0A",'<br>',$post);
				$_POST['ProjectMessages']['message'] = $post;
			
                $model->attributes = Yii::app()->request->getPost('ProjectMessages');
                $model->date = date('Y-m-d H:i:s');
                switch ($model->recipient) {
                	case 'author_to_manager':
                		$model->sender_role = ProjectMessages::model()->getRoleId('Author');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Admin');
                		$model->recipient = 1;
                        break;
                    case 'customer_to_manager':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Customer');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Admin');
                		$model->recipient = 1;
                        break;
                    case 'corrector_to_manager':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Corrector');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Admin');
                        $model->recipient = 1;
                        break;
                    case 'author_to_customer':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Author');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Customer');
                    	$model->recipient = Zakaz::model()->findByPk($orderId)->attributes['user_id'];
                    	break;
                    case 'corrector_to_customer':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Corrector');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Customer');
                    	$model->recipient = Zakaz::model()->findByPk($orderId)->attributes['user_id'];
                    	break;
                    case 'customer_to_author':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Customer');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Author');
                    	$model->recipient = Zakaz::model()->resetScope()->findByPk($orderId)->attributes['executor'];
                    	break;
                    case 'corrector_to_author':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Corrector');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Author');
                    	$model->recipient = Zakaz::model()->resetScope()->findByPk($orderId)->attributes['executor'];
                    	break;
                    case 'author_to_corrector':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Author');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Corrector');
                		$model->recipient = -2;
                		break;
                    case 'customer_to_corrector':
                    	$model->sender_role = ProjectMessages::model()->getRoleId('Customer');
                		$model->recipient_role = ProjectMessages::model()->getRoleId('Corrector');
                		$model->recipient = -2;
                        break;
                }
				$model->save();
                EventHelper::addMessage($orderId, $model->message);
            }
            $this->renderPartial('chat', array(
            	'order'		=> $order,
                'orderId' 	=> $orderId,
				'isGuest'	=> $isGuest,
            ));
            Yii::app()->end();
        }

		if(User::model()->isAuthor() && (!User::model()->isCorrector() || !$order->technicalspec) && !User::model()->isExecutor($orderId)){
			$this->redirect(Yii::app()->createUrl('/project/chat/view',array('orderId'=>$orderId)));
		}
		if(User::model()->isCustomer() && $order->user_id != Yii::app()->user->id) $this->redirect('/');
		
		$parts = ZakazParts::model()->findAll(array(
			'condition' => "`proj_id`='$orderId'",
		));

		if (User::model()->isExecutor($order->id))
			$order->executor_event = null;
		if (User::model()->isCustomer())
			$order->customer_event = null;
		$order->save(false);
		
		$moderate_types = EventHelper::get_moderate_types_string();
        $events = Events::model()->findAll(array(
            'condition' => "`event_id`='$orderId' AND `type` in ($moderate_types)",
            'order' => 'timestamp DESC'
			),
			array(':event_id'=> $orderId) 			
		);
		$PaymentImages = PaymentImage::model()->findAll(array(
			'condition' => "`project_id`='$orderId'",
		));
		$moderated = count($events) == 0;
        $this->render('index', array(
            'orderId'	=> $orderId,
			'order'		=> $order,
            'executor'	=> Zakaz::getExecutor($orderId),
			'moderated'	=> $moderated,
			'parts'		=> $parts,
			'PaymentImages' => $PaymentImages,
			'isCorrector' => $isCorrector,
        ));
    }
	
	public function actionView($orderId) {
		$order = Zakaz::model()->resetScope()->findByPk($orderId);
		$parts = ZakazParts::model()->findAll(array(
			'condition' => "`proj_id`='$orderId'",
		));
		$messageForAuthor = Templates::model()->getTemplate(Templates::TYPE_AUTHOR_RESPONSE_PROJECT);
		//$isGuest = Yii::app()->user->isGuest;
		$isGuest = Yii::app()->user->isGuest();
		if ($isGuest || User::model()->isManager()) {
			Yii::app()->theme='client';
			
			// если гость прошёл по ссылке на неcуществующий
			// проект, отправляем его на регистрацию
			$url = 'http://'.$_SERVER['SERVER_NAME'].'/user/login';
			if (!$order) $this->redirect($url);

			/*$moderate_types = EventHelper::get_moderate_types_string();
			$events = Events::model()->findAll(array(
				'condition' => "`event_id`='$orderId' AND `type` in ($moderate_types)",
				'order' => 'timestamp DESC'
				),
				array(':event_id'=> $orderId) 			
			);
			$moderated = count($events) == 0;
			// если гость прошёл по ссылке на непромодерированный
			// проект, отправляем его на регистрацию
			if (!$moderated) $this->redirect( Yii::app()->createUrl('user/login'));
			*/
		} elseif (Yii::app()->user->id == $order->executor) { // Current executor
			$this->redirect( Yii::app()->createUrl('/project/chat',array('orderId'=>$orderId)));
		}

		if ($isGuest || User::model()->isAuthor())
			$buttonTemplates = Templates::model()->findAllByAttributes(array('type_id'=>($isGuest ? 30 : 29)));

		$this->render('view', array(
			'orderId'	=> $orderId,
			'order'		=> $order,
			//'executor'	=> Zakaz::getExecutor($orderId),
			//'moderated'	=> $moderated,
			'isGuest'	=> $isGuest,
			'parts'		=> $parts,
			'messageForAuthor'	=> $messageForAuthor,
			'buttonTemplates'	=> $buttonTemplates,
		));
        //Yii::app()->end();
	}
	
    /*
     * Rename attachment file
     */
    public function actionApiRenameFile() {
        $this->_prepairJson();
        $data = $this->_request->getParam('data');
		$path=Yii::getPathOfAlias('webroot').$data['dir'];
        if (!file_exists($path)) mkdir($path);
        if (rename($path.$data['name'], $path.'#trash#'.$data['name'])) {
            EventHelper::materialsDeleted($_GET['orderId']);
            $this->_response->setData(true);
        } else $this->_response->setData(false);
        $this->_response->send();
    }

}
