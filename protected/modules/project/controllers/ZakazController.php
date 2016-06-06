<?php

class ZakazController extends Controller {
	/*
	actionView              - просмотр заказа заказчиком после его создания
	actionCreate            - создаёт заказ
	//actionApiRenameFile     - в корзину файл от заказачика
	actionUpdate            - изменение в заказе
	actionUploadPayment     - загрузка чека
	actionPreview           - Превью заказа который должен быть отмодерирован
	actionModerationAnswer  - модерация нового
	actionDelete            - удаляет заказ
	actionIndex             - список заказов (мен)
	getProviders            - формирует списки закзов (для автора и для зак)
	actionOwnList           - заказы исполнителя
	actionCustomerOrderList - заказы заказчика
	actionList              - новые заказы у автора
	actionListTech          - заказы для технических руководителей
	loadModel               - возвращает модель по ID
	actionSpam              - рассылка по авторам  
	actionSetTechSpec       - сохранение и рассылка по техническим руководителям
	actionApiApproveFile    - модерация файла
	actionApiRemoveFile     - удаление файла
	actionUpload            - загрузка файлов в заказе
	actionTree              - страница древовидной структуры
	*/
	/*public function filters() {
        return array(
            'accessControl',
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
                array('allow', // allow authenticated user to perform 'create' and 'update' actions
                    'actions'=>array('view','create', 'uploadPayment', 'list', 'update', 'status','customerOrderList','index','ownList','apiRemoveFile','Upload','ApiRenameFile', ),
                    'users'=>array('@'),
                ),
                array('allow', // allow admin user to perform 'admin' and 'delete' actions
                    'actions'=>array('preview', 'moderationAnswer','apiview','apifindauthor','spam','apiapprovefile','update','status','index','delete', 'deleteFile'),
                    'users'=>array('admin','manager'),
                ),
				array('allow',
					'actions'=>array('upload'),
					'users'=>array('*'),
				),
				array('deny',  // deny all users
					'users'=>array('*'),
				),
			);
	}*/

	protected $_request;
	protected $_response;
	protected function _prepairJson() {
		$this->_request = Yii::app()->jsonRequest;
		$this->_response = new JsonHttpResponse();
	}

	/**
	 * Performs the AJAX validation.
	 * @param Zakaz $model the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='zakaz-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	/*public function actionApiFindAuthor() { // - непонятно зачем нужно (ManagerChat)
		$user = User::model()->findByPk(Yii::app()->user->id);
		$request = Yii::app()->Request;
		if (!$user->superuser)
			$this->redirect('/');
		else {
            $model = Zakaz::model()->findByPk($request->getParam('id'));
			if ($request->getParam('value')=='true')
				$model->status=2;
			else
				$model->status=1;
			$model->save();
		}
	}*/
	/*public function actionApiView() { // - непонятно зачем нужно
		$user = User::model()->findByPk(Yii::app()->user->id);
		if (!$user->superuser) {
			$this->redirect('/');
		} else {
			$this->_prepairJson();
			$sort = $this->_request->getParam('sort');
			$type = $this->_request->getParam('type');
			$searchField = $this->_request->getParam('search_field');
			$searchType = $this->_request->getParam('search_type');
			$searchString = $this->_request->getParam('search_string');
			if (!$sort) {
				$sort = 'date';
			}
			if (!$type) {
				$type = 'DESC';
			}
			if ($searchField == '' || $searchString == '' || $searchType == '') {
				$criteria = new CDbCriteria;
				$criteria->with = array('user','category','job');
				$criteria->order = 't.'.$sort.' '.$type;
				$data = Zakaz::model()->findAll($criteria);
			} else {
				$searchFieldArr=explode('.',$searchField);
				switch ($searchType) {
					case 'bigger':
						$searchType = '<';
						break;
					case 'smaller':
						$searchType = '>';
						break;
					case 'equal':
						$searchType = '=';
						break;
				}
				if (count($searchFieldArr)==3){
					$dbName=ucfirst($searchField[1]);
					$searchString = $dbName::model()->findByAttributes(array($searchFieldArr[0]=>$searchString))->id;
					$searchField=$searchFieldArr[2];
				}
				$criteria = new CDbCriteria;
				$criteria->with = array('user','category','job');
				$criteria->condition='`t`.`'.$searchField.'` '.$searchType.' :scrit';
				$criteria->params=array(':scrit'=>$searchString);
				$criteria->order = '`t`.`'.$sort.'` '.$type;
				$data = Zakaz::model()->findAll($criteria);
			}
			$report = array();
			$report['summary'] = 0;
			$report['ids_count'] = count($data);
			foreach($data as $num=>$dat) {
				$res[$num]=$data[$num]->getAttributes()
				+$data[$num]->user->getAttributes()
				+((isset($data[$num]->category))?$data[$num]->category->getAttributes():array('cat_name'=>'Не определена'))
				+((isset($data[$num]->job))?$data[$num]->job->getAttributes():array('job_name'=>'Не определена'));
				}
			$this->_response->setData( array(
				'data' => $res,
				'report' => $report
			));
			$this->_response->send();
		}
	}*/

	public function actionView($id) { // просмотр заказа заказчиком после его создания
		$model = $this->loadModel($id);
		$projectFields = $model->getFields();
		$this->render('view',array(
			'model'			=> $model,
			'projectFields'	=> $projectFields,
		));
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	static public function createProject($model, $post){
		if(User::model()->isManager()) {
			$model->is_active = 1;
		} else {
			$model->status = 1;
		}
		if(isset($post)) {
			$model->attributes=$post;
			$model->dbdate = date('d.m.Y H:i');
			if (!(User::model()->isManager())) {
				$model->user_id = Yii::app()->user->id;                
				$model->dbmanager_informed = date('d.m.Y H:i');
				$d1=date_create();
				$d2=date_create($model->dbmax_exec_date);
				$interval = (int)($d2->format('U')) - (int)($d1->format('U'));
				$d1->modify('+'.intval($interval/2).' seconds');
				$model->dbauthor_informed = $d1->format('d.m.Y H:i');
			}

            if($model->save()){
                if (!(User::model()->isManager())) {
					Yii::import('project.components.EventHelper');
                    EventHelper::createOrder($model->id);
                }
                $model->moveFiles($model->unixtime/*, $model->id*/);
				
				$user = User::model()->findByPk($model->user_id);
				if ( $user->pid){
					$count_orders = Yii::app()->db->createCommand()
						->select('count(*) AS count')
						->from(Zakaz::model()->tableName())
						->where('user_id=:user_id', array(':user_id'=>$model->user_id))
						->queryRow();
					$count_orders = $count_orders['count'];
					$webmasterlog = new WebmasterLog();
					$webmasterlog->pid = $user->pid;
					$webmasterlog->uid = $user->id;
					$webmasterlog->order_id = $model->id;
					$webmasterlog->date = date("Y-m-d"); 
					if ($count_orders>1)
						$webmasterlog->action =  WebmasterLog::NON_FIRST_ORDER;
					else
						$webmasterlog->action =  WebmasterLog::FIRST_ORDER;
					$webmasterlog->save();
					
				}
                return true;
            } else {
				return false;
			}
			
        }
	}
	 
	public function actionCreate() {
        if(isset($_GET['iframe']) and $_GET['iframe']=='yes') $iframe = true; else $iframe = false;
		$model = new Zakaz();

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

		if( Yii::app()->user->isGuest ) {
			Yii::app()->theme = 'client';
			$user = new RegistrationForm;
			if (isset($_POST['RegistrationForm'])) {
				Yii::import('user.controllers.RegistrationController');
				if (RegistrationController::register($user, $_POST['RegistrationForm'])){
				
				}
			}
		}
		$isGuest = Yii::app()->user->isGuest;
		$new = true;
		$agreementNotAccepted = null;
		$messageForCustomer = null;
		if (!$isGuest && self::createProject($model,$_POST['Zakaz'])) {
			if (User::model()->isManager()) {
				$this->redirect(Yii::app()->createUrl('/project/zakaz/update', array('id'=>$model->id)));
			} else {
				$new = false;
				$agreementNotAccepted = Templates::model()->getTemplate(Templates::TYPE_FOR_MANAGER_AGREEMENT_NOT_ACCEPTED);;
				$messageForCustomer = Templates::model()->getTemplate(Templates::TYPE_FOR_CUSTOMER_AGREEMENT_NOT_ACCEPTED);
			}
		}
		else $model->attributes = $_POST['Zakaz'];
		if (!isset($model->unixtime) || $model->unixtime=='' ) {
			$model->unixtime = time();
		}

		if( $iframe ) $this->layout = '//layouts/iframe';
        $this->render('create',array(
            'model'=>$model,
			'isGuest' => $isGuest,
			'user' => $user,
			'new' => $new,
			'agreementNotAccepted' => $agreementNotAccepted,
			'messageForCustomer' => $messageForCustomer,
        ));
	}
    
    /*public function actionApiRenameFile() { dubl in chatController
        $this->_prepairJson();
        $data = $this->_request->getParam('data');
        $path=Yii::getPathOfAlias('webroot').$data['dir'];
        if (!file_exists($path)) mkdir($path);
        if (rename($path.$data['name'], $path.'#trash#'.$data['name'])) {
            EventHelper::materialsDeleted($_GET['orderId']);
            $this->_response->setData(true);
        } else $this->_response->setData(false);
        $this->_response->send();
    }*/
	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		if (isset($_GET['sid'])) { // Меняем статус, ajax
			$sid = $_GET['sid'];
			$model = $this->loadModel($id);
			$model->status = $sid;
			$model->save();
			Yii::app()->end();
		}
        if (Yii::app()->request->isAjaxRequest) {
            //echo 'test';
            $data = Yii::app()->request->getRestParams();
			$field = str_replace('Zakaz_','',$data['elid']);
            if (is_array($data)) {
                $model=$this->loadModel($data['id']);
                echo json_encode($model->$field=$data['data']);
				echo json_encode($model->save());
				echo json_encode($model->errors);
                Yii::app()->end();
            }
            $this->renderPartial('_order_list_update');
            Yii::app()->end();
            
        }
        
		Yii::app()->session['project_id'] = $id;
		$model=$this->loadModel($id);
		
        if (Yii::app()->request->getParam('close') == 'yes'){
            $model->old_status = $model->status;
			$model->status = 5;
			$model->save(false);
			$user = User::model()->findByPk($model->user_id);
			if($user->pid) {
				$payed = Payment::model()->exists('order_id = :p1 AND payment_type = :p2', array(':p1'=>$model->id, ':p2'=>Payment::OUTCOMING_WEBMASTER));
				if ( !$payed ) { // Only first time
					$webmaster = User::model()->with('profile')->findByPk($user->pid);
					$openlog = WebmasterLog::model()->findByAttributes(	array('order_id'=>$model->id),
						'action = :p1 OR action = :p2', array(':p1'=>WebmasterLog::FIRST_ORDER, ':p2'=>WebmasterLog::NON_FIRST_ORDER)
					);
					$webmasterlog = new WebmasterLog();
					$webmasterlog->pid = $user->pid;
					$webmasterlog->uid = $user->id;
					$webmasterlog->date = date("Y-m-d"); 
					$webmasterlog->order_id = $model->id;
					if($openlog->action == WebmasterLog::FIRST_ORDER){
						$webmasterlog->action = WebmasterLog::FINISH_FIRST_ORDER_SUCCESS;
					}elseif($openlog->action == WebmasterLog::NON_FIRST_ORDER){
						$webmasterlog->action = WebmasterLog::FINISH_NON_FIRST_ORDER_SUCCESS;
					}
					$webmasterlog->save();
					// Pament for webmaster ~~~~~~~~~~~~~~~~~~~~~~~~~~
					$payment = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
						':ORDER_ID'=>$model->id
					));
					$manag = User::model()->findByPk(Yii::app()->user->id);
					$buh = new Payment;
					$buh->order_id = $model->id;
					$buh->receive_date = date('Y-m-d');
					$buh->theme = $model->title;
					$buh->user = $webmaster->email;
					$buh->details_ya = $webmaster->profile->yandex;
					$buh->details_wm = $webmaster->profile->wmr;
					$buh->details_bank = $webmaster->profile->bank_account;
					$buh->payment_type = Payment::OUTCOMING_WEBMASTER;
					$buh->manager = $manag->email;
					//$buh->approve = 0;
					$buh->method = 'Cash or Bank';
					if($openlog->action == WebmasterLog::FIRST_ORDER){
						$buh->summ = (float) $payment->project_price * Company::getWebmasterFirstOrderRate();
					}elseif($openlog->action == WebmasterLog::NON_FIRST_ORDER){
						$buh->summ = (float) $payment->project_price * Company::getWebmasterSecondOrderRate();
					}
					$buh->save();
				}
			}
			$this->redirect(array('update','id'=>$model->id));
		} elseif (Yii::app()->request->getParam('open') == 'yes'){
			$model->status = $model->old_status;
			$model->save(false);
			$this->redirect(array('update','id'=>$model->id));
		} elseif (Yii::app()->request->getParam('refound') == 'yes'){
            $model->old_status = $model->status;
			$model->status = 5;
			$model->save(false);
			$user = User::model()->findByPk($model->user_id);
			// Refound ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			$manag = User::model()->findByPk(Yii::app()->user->id);
			$payment = ProjectPayments::model()->find('order_id = :ORDER_ID', array(
				':ORDER_ID'=>$model->id
			));
			if ($payment && $payment->received >0) {
				$refound = $payment->received;
				$payment->received = 0;
				$payment->save();
				$buh = new Payment;
				$buh->order_id = $model->id;
				$buh->receive_date = date('Y-m-d');
				$buh->theme = $model->title;
				$buh->user = $user->email;
				$buh->summ = ((float) $refound);
				$buh->payment_type = Payment::OUTCOMING_CUSTOMER;
				$buh->manager = $manag->email;
				//$buh->approve = 0;
				$buh->method = 'Cash or Bank';
				$buh->save();
			}
			if($user->pid) {
				$webmasterlog = new WebmasterLog();
				$webmasterlog->pid = $user->pid;
				$webmasterlog->uid = $user->id;
				$webmasterlog->date = date("Y-m-d"); 
				$webmasterlog->order_id = $model->id;
				$openlog = WebmasterLog::model()->findByAttributes(	array('order_id'=>$model->id),
					'action = :p1 OR action = p2', array(':p1'=>WebmasterLog::FIRST_ORDER, ':p2'=>WebmasterLog::NON_FIRST_ORDER)
				);
				if($openlog->action == WebmasterLog::FIRST_ORDER){
					$webmasterlog->action = WebmasterLog::FINISH_FIRST_ORDER_FAILURE;
				}elseif($openlog->action == WebmasterLog::NON_FIRST_ORDER){
					$webmasterlog->action = WebmasterLog::FINISH_NON_FIRST_ORDER_FAILURE;
				}else{
					echo 'Somthing wrong...';
					Yii::app()->end();
				}
				$webmasterlog->save();
			}
			$this->redirect(array('update','id'=>$model->id));
		}


		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
		if(isset($_POST['Zakaz'])) {
			$model->attributes = $_POST['Zakaz'];

			if(isset($_POST['Zakaz']['dbdate']))
				$model->dbdate = $_POST['Zakaz']['dbdate'];

			$projectFields = $model->getFields();
			if ($projectFields) foreach($projectFields as $field) {
				if ($field->field_type=="TIMESTAMP") {
					// ----------------------------------------------------
					$tmp = $field->varname;
					if (isset($_POST['Zakaz'][$tmp])) {
						$model->$tmp = $_POST['Zakaz'][$tmp];
						$model->timestampInput($field);
					}
				}
			}

			if($model->save()) {
				if (Yii::app()->request->getParam('accepted') && User::model()->isCorrector())
					EventHelper::correctorAccepted($model->id);

				$role = User::model()->getUserRole();
				if ($role != 'Manager' && $role != 'Admin') {
// где-то есть дублрующий вызов записи события, поэтому этот комментируем
// oldbadger 09.10.2015					
//					EventHelper::editOrder($model->id);
					//$view = 'orderInModerate';
					$this->redirect(array("../project/chat?orderId=$id"));
				} else {
//					$this->redirect(array('project/chat','orderId'=>$model->id));
					$this->redirect(array('update','id'=>$model->id));
				}
			}
			
		}
		
		$managerlog = new ManagerLog();
		$managerlog->uid = Yii::app()->user->id;
		$managerlog->action = ManagerLog::ORDER_PAGE_VIEW;
		$managerlog->datetime = date('Y-m-d H:i:s'); 
		$managerlog->order_id = $model->id;
		$managerlog->save();

		$hints = Templates::model()->getTemplateList(4);
		$view = 'update';
		$isModified = false;
		$this->render($view, array(
			'model'=>$model,
			'hints'=>$hints,
			//'message'=>$model->projectStatus->status,
			'isModified'=>$isModified,
		));
	}
    
    public function actionUploadPayment($id) {
		$folder = Yii::getPathOfAlias('webroot').PaymentImage::getFolder();
		$result = Tools::uploadMaterials($folder,false);
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		if ($result['success'] && User::model()->isCustomer()) {
            $paymentImage = new PaymentImage;
            $paymentImage->project_id = $id;
            $paymentImage->image = $result['fileName'];
            $paymentImage->save(false);
			EventHelper::chekUploaded($id);
		}
    }

    /**
     * Превью заказа который должен быть отмодерирован
     * @param $id
     * @author Emericanec
     */
    public function actionPreview($id)
    {
            
        $event = Events::model()->findByPk($id);

        if (!$event) {
            throw new CHttpException(404, "Событие не найдено");
        }
        
        if ($event->type == EventHelper::TYPE_CUSTOMER_REGISTRED) {
            $rid=$event->event_id;
            $event->delete();
            $this->redirect(['/user/admin/update', 'id' => $rid]);
        }
        if ($event->type == EventHelper::TYPE_MESSAGE) {
            $rid=$event->event_id;
            $event->delete();
            $this->redirect(['/project/zakaz/update', 'id' => $rid]);
        }

        $model = Zakaz::model()->resetScope()->findByPk($event->event_id);
        if (!$model->is_active) {
			$user = User::model()->findByPk($model->user_id);
            $this->render('preview', array(
                'model' => $model,
				'user' => $user,
                'event' => $event
            ));
        } else {
            $rid=$event->event_id;
            $event->delete();
            $this->redirect(['/project/zakaz/update', 'id' => $rid]);
        }
    }
    /**
     * Одобрение или нет заказа
     * @param $answer
     * @author Emericanec
     */
    public function actionModerationAnswer($id, $event_id, $answer){
        $model = Zakaz::model()->resetScope()->findByPk($id);
        $event = Events::model()->findByPk($event_id);
        if(!$model->is_active && $event) {
            
            if ($answer == 1) {
                $model->is_active = 1;
                
                if($model->save()) {
                    $event->delete();
					
					// Заказчику проект принят					
					$type_id = Emails::TYPE_12;
					$email = new Emails;
						
					$order= Zakaz::model()->findByPk($id);
					$user = User::model()->findByPk($order->user_id);

					$email->from_id = 1;
					$email->to_id   = $user->id;
						
					$rec   = Templates::model()->findAll("`type_id`='$type_id'");
					$campaign = Campaign::search_by_domain($_SERVER['SERVER_NAME']);
					$email->campaign = $campaign->name;
					$email->name = $user->full_name;;
					$email->num_order = $id;
					$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$id;
					
					//$email->login= $user->username;
					//$email->password= $soucePassword;
					$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $type_id);

					$this->redirect(Yii::app()->createUrl('project/zakaz/update', array(
                        'id' => $model->id
                    )));
                }
					
            } else {
                // если нет то просто удаляем
                $model->delete();
                $event->delete();
                $this->redirect(Yii::app()->createUrl('project/event'));
            }
        }else{
            $event->delete();
            throw new CHttpException("Заказ не найден или его уже отмодерировали");
        }
    }

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!Yii::app()->getRequest()->getParam('ajax',false))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex($all=0) {
        $model = new Zakaz('search');
        $model->unsetAttributes();
		if($all == 1) $model->setAttribute('status', -1);
        if(Yii::app()->request->isAjaxRequest) {

            array_walk($_POST['Zakaz'],function(&$v,$k){
                if (substr($k,0,2))
                    if (strlen($v)>10) $v=substr($v,0,10);
            });
			$params = Yii::app()->request->getParam('Zakaz');
            $model->setAttributes($params);
			Yii::app()->user->setState('ZakazFilterState', $params);
            $this->renderPartial('index', array(
                'model' => $model,
            ), false, true);
        }
        else {
			$params = Yii::app()->user->getState('ZakazFilterState');
			if ( isset($params) ) {
				$model->setAttributes($params);
			}
            $this->render('index',array(
                'model'=>$model,
            ));
		}
	}
/*	
	Статусы проекта:
	1=Новый заказ,
	2=Ждем решен. клиента,
	3=Поиск Автора,
	4=Автор работает,
	5=Завершен,	
*/	
    public function getProviders($new=false)
    {
		if ($new) { $arr = array(3); $sarr= '3'; } else	{
					$arr = array(1,2,3,4); $sarr= '1,2,3,4';
		};
		$uid		= Yii::app()->user->id;

        $criteria = new CDbCriteria();
		$criteria->addInCondition('status',$arr);
		
        if ($new) {
			$criteria->compare('executor', 0);
			$user = User::model()->with('profile')->findByPk(Yii::app()->user->id);
			if (ProjectField::model()->inTableByVarname('specials') && isset($user->profile->specials) && $user->profile->specials)
			{
				$specials = explode(',',$user->profile->specials);
				$criteria->addInCondition('specials',$specials);
			}
			if (ProjectField::model()->inTableByVarname('specials2') && isset($user->profile->specials2) && $user->profile->specials2)
			{
				$specials2 = explode(',',$user->profile->specials2);
				$criteria->addInCondition('specials2',$specials2);
			}
		} else {
			if (User::model()->isAuthor())	$criteria->compare('executor', $uid);
			if (User::model()->isCustomer())$criteria->compare('user_id',  $uid);
		};	
		$criteria->addInCondition('status',$arr);

        $dataProvider = new CActiveDataProvider(Zakaz::model()->resetScope(), [
            'criteria' => $criteria,
			'pagination' => false
        ]);
		$result = array('model'=>$dataProvider, 'model_done'=> null);
		$criteria->addInCondition('status',$arr);
        
		$criteria_done = new CDbCriteria();
		if (User::model()->isAuthor())	$criteria_done->compare('executor', $uid);
		if (User::model()->isCustomer())$criteria_done->compare('user_id',  $uid);
		$criteria_done->addInCondition('status',array(5));

        $dataProvider_done = new CActiveDataProvider(Zakaz::model()->resetScope(), [
            'criteria' => $criteria_done,
			'pagination' => false
        ]);
		$result['model_done'] = $dataProvider_done;
		return	$result;
    }

	/**
	 * Manages all models.
	 */
	/*public function actionAdmin()
	{
		$model=new Zakaz('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Zakaz']))
			$model->attributes=$_GET['Zakaz'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}*/

	public function actionOwnList()
	{
		$models = $this->getProviders(0);
        $this->render('list', [
            'model' => $models['model'],
            'model_done' => $models['model_done'],
            'dataProvider' => $models['model'],
            'dataProvider_done' => $models['model_done'],
        ]);
	}
	
    public function actionCustomerOrderList()
    {
		$models = $this->getProviders();
		
        $this->render('customerOrderList', [
            'model' => $models['model'],
            'model_done' => $models['model_done'],
            'dataProvider' => $models['model'],
            'dataProvider_done' => $models['model_done'],
        ]);
    }

	public function actionList($status=0) {
		$new 	= true;//User::model()->isAuthor();
		$models = $this->getProviders(true);
		$model	=  $models['model'];
		$user = User::model()->with('profile')->findByPk(Yii::app()->user->id);
		$isGuest = Yii::app()->user->isGuest();
		if ($isGuest) {
			Yii::app()->theme='client';
		}
		$this->render('list',array(
			'model'=>$models['model'],
            'model_done' => $models['model_done'],
            'dataProvider' => $models['model'],
            'dataProvider_done' => $models['model_done'],
			'only_new'		=> $new,
			'profile' => $user->profile,
			'isGuest' => $isGuest,
		));
	}

	public function actionListTech() {
		$new = true;
		$user = User::model()->with('profile')->findByPk(Yii::app()->user->id);

        $criteria = new CDbCriteria();
        if (ProjectField::model()->inTableByVarname('specials') && isset($user->profile->specials) && $user->profile->specials)
		{
			$specials = explode(',',$user->profile->specials);
			$criteria->addInCondition('specials',$specials);
		}
		if (ProjectField::model()->inTableByVarname('specials2') && isset($user->profile->specials2) && $user->profile->specials2)
		{
			$specials2 = explode(',',$user->profile->specials2);
			$criteria->addInCondition('specials2',$specials2);
		}
		// $criteria->compare('executor', '<>'.$user->id);
		$criteria->compare('technicalspec', 1);

        $dataProvider = new CActiveDataProvider(Zakaz::model()->resetScope(), [
            'criteria' => $criteria,
			'pagination' => false
        ]);
		$this->render('list',array(
			'model'=>$dataProvider,
            'model_done' => null,
            'dataProvider' => $dataProvider,
            'dataProvider_done' => null,
			'profile' => $user->profile,
			'only_new' => $new,
			'tech' => 1,
		));
	}

	/*public function actionDownload()
	{
	   EDownloadHelper::download($_GET['path']);
			$this->redirect(Yii::app()->request->urlReferrer);
	}*/

    public function actionSpam($orderId) {

		header('Content-type: application/json');
        
        $order = Zakaz::model()->findByPk($orderId);

        if (!$order) {
            throw new CHttpException(500);
        }
		$order->status = 3;
		$order->last_spam = date("Y-m-d H:i:s");
		$order->save();
		
		$criteria = new CDbCriteria();
		$projectFields = $order->getFields();
		$spamFields = array();
		if ($projectFields) 
			foreach($projectFields as $field) {
				if ($field->required==ProjectField::REQUIRED_YES_REG_SPAM) {
					$varname = $field->varname;
					$value = $order->$varname;
					//$criteria->addSearchCondition('profile.'.$varname,$value,true);
					//$criteria->addCondition('profile.'.$varname.' REGEXP \'(^|[[:punct:]])'.$value.'($|[[:punct:]])\'');
					$spamFields[] = $varname;
				}
			}
		$criteria->addSearchCondition('AuthAssignment.itemname','Author');
		$criteria->addSearchCondition('profile.mailing_for_executors','1');
		$authors = User::model()->with('AuthAssignment','profile')->findAll($criteria);

		if(!empty($authors)) {

            /*$link = $this->createAbsoluteUrl('/project/chat/', ['orderId' => $orderId]);
            $mail = new YiiMailer('invite', ['link' => $link]);
			$mail->clearLayout();
            $mail->setFrom(Yii::app()->params['supportEmail'], Yii::app()->name);
            $mail->setSubject('Приглашение в проект');
			$link = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
            $mail->setBody('<a href="'.$link.'">'.$link.'</a>');
            foreach ($authors as $author) {
//                $mail->setTo($author->email);
//                if($author->getUserRole($author->id)=='Author') $mail->send();
            }*/
            
			// новая рассылка

			$typeId = Emails::TYPE_18;
			$rec   = Templates::model()->findAll("`type_id`='$typeId'");
		
            foreach ($authors as $user) {
				
				foreach ($spamFields as $field) {
					if(isset($user->profile) && $user->profile->$field) {
						$specials = explode(',',$user->profile->$field);
						if (!in_array($order->$field, $specials)) continue 2;
					}
				}
				$email = new Emails;

				$email->to_id = $user->id;

				$email->name = $user->full_name;
				if (strlen($email->name) < 2) $email->name = $user->username;
				$email->login= $user->username;
				$email->num_order = $orderId;
				$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
				$specials = (isset($order->specials))?$order->specials:$order->specials2;
				$specials = Catalog::model()->findByPk($specials);
				$email->specialization	= $specials->cat_name;
				$email->name_order		= $order->title;		
				$email->subject_order	= $order->title;		
				$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $typeId);
			}	
        } else {
             echo json_encode(array('error'=>'Нет авторов'));
        }
        
        Yii::app()->end();
    }

    public function actionSetTechSpec() {
    	$orderId = $_POST['orderId'];
    	$val = $_POST['val'];

    	$order = Zakaz::model()->findByPk($orderId);
        if (!$order) {
            throw new CHttpException(500);
        }
		$order->technicalspec = $val;
		$order->save();

		if ($val)
		{
			$criteria = new CDbCriteria();
	        if(Company::getId()) {
				$projectFields = $order->getFields();
				if ($projectFields) 
					foreach($projectFields as $field) {
						if ($field->required==ProjectField::REQUIRED_YES_REG_SPAM) {
							$varname = $field->varname;
							$value = $order->$varname;
							$criteria->addSearchCondition('profile.'.$varname,$value);
						}
					}
			}
			$criteria->addSearchCondition('AuthAssignment.itemname', 'Corrector');
			$authors = User::model()->with('profile','AuthAssignment')->findAll($criteria);

			if(!empty($authors)) {
	            /*$link = $this->createAbsoluteUrl('/project/chat/', ['orderId' => $orderId]);
	            $mail = new YiiMailer();
				$mail->clearLayout();
	            $mail->setFrom(Yii::app()->params['supportEmail'], Yii::app()->name);
	            $mail->setSubject('Приглашение в проект');
				$link = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
	            $mail->setBody('<a href="'.$link.'">'.$link.'</a>');*/
	            
				// новая рассылка

				$typeId = Emails::TYPE_26;
				$rec   = Templates::model()->findAll("`type_id`='$typeId'");
			
	            foreach ($authors as $user) {
					//$specials = explode(',',$user->profile->specials);
					//if (!in_array($order->specials, $specials)) continue;
					
					$email = new Emails;
					$email->to_id = $user->id;
					$email->name = $user->full_name;
					if (strlen($email->name) < 2) $email->name = $user->username;
					$email->login= $user->username;
					$email->num_order = $orderId;
					$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
					$specials = (isset($order->specials))?$order->specials:$order->specials2;
					$specials = Catalog::model()->findByPk($specials);
					$email->specialization	= $specials->cat_name;
					$email->name_order		= $order->title;		
					$email->subject_order	= $order->title;		
					$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $typeId);
				}
				echo 'send_email';
	        }
	        else
	        	echo 'no_users';
	    }
        Yii::app()->end();
    }

    public function actionApiApproveFile() {
        $this->_prepairJson();
        $data = $this->_request->getParam('data');
		// --- campaign
		if(isset(Zakaz::$files_folder)){
			$path=Yii::getPathOfAlias('webroot').Zakaz::$files_folder.$data['id'].'/';
		} else {
			$path=Yii::getPathOfAlias('webroot').'/uploads/'.$data['id'].'/';
		}
		// ---
        //$path=Yii::getPathOfAlias('webroot').'/uploads/'.$data['id'].'/';
        if (!file_exists($path)) mkdir($path);
        if (rename($path.$data['name'], $path.str_replace('#pre#', '', $data['name']))) {
            $this->_response->setData(true);
        } else $this->_response->setData(false);
        $this->_response->send();
    }
    
    /*
     * Remove attachment file
     */
    public function actionApiRemoveFile() {
        $this->_prepairJson();
        $data = $this->_request->getParam('data');
		if(isset(Zakaz::$files_folder)){
			$path=Yii::getPathOfAlias('webroot').Zakaz::$files_folder.$data['id'].'/';
		} else {
			$path=Yii::getPathOfAlias('webroot').'/uploads/'.$data['id'].'/';
		}
        if (!file_exists($path)) mkdir($path);
        if (unlink($path.$data['name'])) {
            $this->_response->setData(true);
        } else $this->_response->setData(false);
        $this->_response->send();
    }


    public function actionUpload() {
		if($_GET['id']) $id = intval($_GET['id']);
		if($_GET['unixtime']) $unixtime = intval($_GET['unixtime']);
		$folder = Yii::getPathOfAlias('webroot').'/uploads/c'.Company::getId();
		if ($id)
			$folder .= '/'.$id.'/';
		else
			$folder .= '/temp/'.$unixtime.'/';
		$result = Tools::uploadMaterials($folder);
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		if ($id && $result['success'] && User::model()->isCustomer()) {
			EventHelper::materialsAdded($id);
		}
    }
	
	public function actionTree() {
		//if(User::model()->isCustomer())
		$orders = Zakaz::model()->findAllByAttributes(['user_id'=>Yii::app()->user->id]);

		$trees = array();
		$forest = array();
		$childs = array();// order_id => array(childs)
		foreach ($orders as $order){
			//if(!in_array($order->id, $picked, true)) {
				//while ($order) {
					//$picked[] = $order->id;
					$trees[$order->id] = $order->title;
					if($order->parent_id) {
						$childs[$order->parent_id][] = $order->id;
						//$order = Zakaz::model()->findAllByAttributes(['id'=>$order->parent_id]);
					}
					else {
						$forest[] = $order->id;
					}
				//}
			//}
		}
		//$forest
		$this->render('tree',array(
			'forest'=> $forest,
            'trees' => $trees,
            'childs' => $childs,
            //'dataProvider_done' => null,
			//'profile' => $user->profile,
			//'only_new' => $new,
			//'tech' => 1,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Zakaz the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id) {
		$model = Zakaz::model()->resetScope()->findByPk($id);
		if($model===null){
            throw new CHttpException(404,Yii::t('site','The requested page does not exist.'));
        }
		return $model;
	}
}
