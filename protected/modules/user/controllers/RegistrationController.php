<?php

class RegistrationController extends Controller
{
	public $defaultAction = 'registration';

    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='simple-registration-form') {
			echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    } 
	
	/**
	 * Registration user
	 */
	static public function register($model, $post, $role = 'Customer'){
		$model->attributes = $post;
		if(isset($_COOKIE['partner'])) $model->pid = intval($_COOKIE['partner']);

		if($model->validate()) {
			$soucePassword = UserModule::generate_password(8);
			$model->password=UserModule::encrypting($soucePassword);
			$model->superuser=0;
			$model->status=1;
			
			if ($model->save()) {
				$AuthAssignment = new AuthAssignment;
				$AuthAssignment->attributes=array('itemname'=>$role,'userid'=>$model->id);
				$AuthAssignment->save();
				
				if ($role == 'Author') {
					if($model->profile == null) {
						$profile = new Profile;
						$profile->user_id = $model->id;
						$profile->mailing_for_executors = 1;
						$profile->save();
					}
				}
				
				$webmasterlog = new WebmasterLog();
				$webmasterlog->pid = $model->pid;
				$webmasterlog->uid = $model->id;
				$webmasterlog->date = date("Y-m-d"); 
				$webmasterlog->action =  WebmasterLog::REG;
				$webmasterlog->save();

				// новая служба системных сообщений
				$type_id = Emails::TYPE_11;
				$email = new Emails;
				
				$criteria = new CDbCriteria();
				$criteria->order = 'id DESC';
				$criteria->limit = 1;
				$user = User::model()->findAll($criteria);
				$user = $user[0];

				$email->from_id = 1;
				$email->to_id   = $user->id;
				
				$rec   = Templates::model()->findAll("`type_id`='$type_id'");
				$id = Company::getId();
				$email->company = Company::getName();
				$email->name = $model->full_name;
				$email->login= $model->email;
				$email->password= $soucePassword;
				
				$email->page_cabinet = 'http://'.$_SERVER['SERVER_NAME'].'/user/profile/edit';
				$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $type_id);
				
				$identity=new UserIdentity($model->email,$soucePassword);
				$identity->authenticate();
				Yii::app()->user->login($identity,0);
				//$this->redirect(Yii::app()->controller->module->returnUrl[0]);
				return true;
				//Yii::app()->end();
			} else {
				//Yii::app()->user->setFlash('reg_failed',UserModule::t("Sorry, something wrong... :("));
				//$this->refresh();
				echo 'Cant save';
				Yii::app()->end();
			}
		} else {
			return false;
		}
	}
	public function actionRegistration() {
		$model = new RegistrationForm;
		$this->performAjaxValidation($model);
		if(isset($_GET['role']) && $_GET['role']=='Customer') {
			$role = 'Customer';
		} elseif(isset($_GET['role']) && $_GET['role']=='Author') {
			$role = 'Author';
		/*} elseif(isset($_GET['role']) && $_GET['role']=='Manager') {
			$role = 'Manager';*/
		} elseif(isset($_GET['role']) && $_GET['role']=='Webmaster') {
			$role = 'Webmaster';
		} else {
			$role = 'Customer';
		}
		if (Yii::app()->user->id && (!Yii::app()->user->hasFlash('reg_success') && !Yii::app()->user->hasFlash('reg_failed'))) {
			if($role == 'Author')
				$this->redirect('/project/zakaz/list');
			else
				$this->redirect(Yii::app()->controller->module->profileUrl);
		} else {
			if (isset($_POST['RegistrationForm'])) {
				if (self::register($model, $_POST['RegistrationForm'], $role)){
					Yii::import('project.components.EventHelper');
					if($role == 'Customer') EventHelper::newCustomer();
					Yii::app()->user->setFlash('reg_success',UserModule::t("Thank you for your registration. Password has been sent to your e-mail. Please check your e-mail ({{email}}) before start.", ['{{email}}'=>$model->email]));
					$this->refresh();
				} else {
					$message = UserModule::t("Sorry, something wrong... :(");
					$errors = $model->errors;
					if(isset($errors['email'])) $message = $errors['email'][0];
					//Yii::app()->end();
					Yii::app()->user->setFlash('reg_failed',$message);
					//$this->refresh();
				}
			}
			
			Yii::app()->theme='client';
			$this->render('/user/registration',array('model'=>$model, 'role'=>$role));
		}
	}
	
}
