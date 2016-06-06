<?php

class ProfileController extends Controller
{
	public $defaultAction = 'edit';//'profile';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_model;
	/**
	 * Shows a particular model.
	 */
	/*public function actionProfile()
	{
		$model = $this->loadUser();
	    $this->redirect('/');
	}*/


	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	 
	public function actionEdit() {
		$model = $this->loadUser();
		
		if($model->profile == null) {
			$model->profile = new Profile;
			$model->profile->user_id = $model->id;
		}
		$profile=$model->profile;
		// ajax validator
		if(isset($_POST['ajax']) && $_POST['ajax']==='profile-form')
		{
			echo UActiveForm::validate(array($model,$profile));
			Yii::app()->end();
		}
		
		if(isset($_POST['User']))
		{
			$model->attributes=$_POST['User'];
			$profile->attributes=$_POST['Profile'];

			$profile->notification = $_POST['Profile']['notification'];
			
			if($model->validate()&&$profile->validate()) {
                //Yii::app()->user->updateSession();
				$model->save();
				$profile->save();
				Yii::app()->user->setFlash('profileMessage',ProjectModule::t('Update profile'));
				//$this->redirect(array('/user/profile'));
			} else {
				$profile->validate();
			};	
		}

		$this->render('edit',array(
			'model'=>$model,
			'profile'=>$profile,
		));
	}
	
	/**
	 * Change password
	 */
	/*public function actionChangepassword() {
		$model = new UserChangePassword;
		if (Yii::app()->user->id) {
			
			// ajax validator
			if(isset($_POST['ajax']) && $_POST['ajax']==='changepassword-form')
			{
				echo UActiveForm::validate($model);
				Yii::app()->end();
			}
			
			if(isset($_POST['UserChangePassword'])) {
					$model->attributes=$_POST['UserChangePassword'];
					if($model->validate()) {
						$new_password = User::model()->notsafe()->findbyPk(Yii::app()->user->id);
						$new_password->password = UserModule::encrypting($model->password);
						$new_password->activkey=UserModule::encrypting(microtime().$model->password);
						$new_password->save();
						Yii::app()->user->setFlash('profileMessage',UserModule::t("New password is saved."));
						$this->redirect(array("profile"));
					}
			}
			$this->render('changepassword',array('model'=>$model));
	    }
	}*/

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
	 */
	public function loadUser() {
		if($this->_model===null)
		{
			if(Yii::app()->user->id)
				$this->_model=Yii::app()->controller->module->user();
			if($this->_model===null)
				$this->redirect(Yii::app()->controller->module->loginUrl);
		}
		return $this->_model;
	}
    
    /*
     * отображение денежных средств
     * отображаем только для автора\заказчика
     */
    /*public function actionAccount()
    {
        
        if (User::model()->isCustomer()) {
            
            $sql = 'SELECT SUM(project_price) AS project_price, SUM(to_receive) AS to_receive, SUM(received) AS received FROM `ProjectPayments` WHERE order_id IN (SELECT id FROM Projects WHERE user_id = :user_id)';
            
            $columns = [
                'project_price',
                'to_receive',
                'received'
            ];
            
        } elseif (User::model()->isAuthor()) {
            
            $sql = 'SELECT SUM(work_price) AS work_price, SUM(to_pay) AS to_pay FROM `ProjectPayments` WHERE order_id IN (SELECT id FROM Projects WHERE executor = :user_id)';
            
            $columns = [
                'work_price',
                'to_pay'
            ];
            
        } else {
            throw new CHttpException(500);
        }
        
        $command = Yii::app()->db->createCommand($sql);
        $command->params = [':user_id'=>Yii::app()->user->id];
        $result = $command->queryRow();
        
        foreach ($columns as $column) {
            $params[$column] = (int)$result[$column];
        }
        
        $this->render('account', $params);
    }*/
    /*
     * список изменений в профиле пользователя
     */
	/*public function actionPreviewUpdate($id) {
		$models = Moderate::model()->findAllByAttributes(['event_id'=>$id]);

		$user = User::model()->findbyPk($id);
		$this->render('previewUpdate',array('models'=>$models,'user'=>$user));
	}*/
	/*
     * меняем статус изменения в профиле
     */
	/*public function actionChStatus($id,$status){
		$model = UpdateProfile::model()->findByPk($id);
		if ($status == 'appove') {
			$model->status = 1;
			$attribute = $model->attribute;
			$profile = Profile::model()->findbyPk($model->user);
			$profile->$attribute = $model->to_data;
			if ($profile->save(false)) $model->save();
			if (YII_DEBUG) CVarDumper::dump($profile->errors);
		}

		if ($status == 'reject') {
			$model->status = 0;
			$model->save();
		}

		$countRecord = UpdateProfile::model()->countByAttributes(array(
			'user'=>$model->user,
			'status'=>null,
		));

		//если записей для редактирования нет, то удаляем сообщения из списка сообщений
		if ($countRecord <= 0){
			// сменим всем событиям статус на выполненый у данного пользователя
			Yii::import('application.modules.project.components.EventHelper');
			$eventList = Events::model()->findAllByAttributes(array(
				'type' => EventHelper::TYPE_UPDATE_PROFILE,
				'event_id' => $model->user,
			));

			foreach ($eventList as $event) {
				$event->delete();
			}
		}
		// ответ
		if (Yii::app()->request->isAjaxRequest) {
			Yii::app()->end();
		}
		Yii::app()->redirect(Yii::app()->request->urlReferrer);
	}*/
}