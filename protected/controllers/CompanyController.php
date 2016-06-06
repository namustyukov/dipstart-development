<?php
class CompanyController extends Controller {
	/**
	 * @return array action filters
	 */
	public function filters() {
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('edit'),
				'users'=>UserModule::getAdminsAndRoot(),
			),
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('list','create','sql'),
				'users'=>array('root'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionList() {
		$model = new Company('search');
		$model->unsetAttributes();
		if(Yii::app()->request->isAjaxRequest) {
			$params = Yii::app()->request->getParam('Company');
			$model->setAttributes($params);
			Yii::app()->user->setState('CompanyFilterState', $params);
		}
		$this->render('list',array(
			'model'=>$model,
		));
	}
	
	public function actionEdit($id = false) {
		if($id) {
			$model = Company::model()->findByPk($id);
		} else {
			$model = Company::getCompany();
		}
		if(isset($_POST['ajax']) && $_POST['ajax']==='company-form') {
			echo UActiveForm::validate(array($model));
			Yii::app()->end();
		}
		if(isset($_POST['Company'])) {
			$model->attributes = $_POST['Company'];
			$model->fileupload = CUploadedFile::getInstance($model, 'fileupload');
			$model->iconupload = CUploadedFile::getInstance($model, 'iconupload');
			if($model->validate()) {
				$model->save();
				Yii::app()->user->setFlash('companySuccessMessage',ProjectModule::t('Successfully updated'));
				//$this->redirect(array('/user/profile'));
			} else {
				Yii::app()->user->setFlash('companyErrorMessage',ProjectModule::t('Something wrong'));
			};	
		}
		//Yii::app()->theme = 'admin';
		$this->render('edit',array(
			'model'=>$model,
			'root'=>(User::model()->getUserRole()=='root'),
		));
	}
	public function actionCreate() {
		if(isset($_POST['company_to_copy']) && $_POST['company_to_copy']) {
			$copy_data_list = array('Templates', 'Сatalog','ProjectStatus','PartStatus','ProjectFields','ProfilesFields');
			$prefix = intval($_POST['company_to_copy']).'_';
			$model = new Company();
			$model->organization = 1;
			$model->name = 'New company';
			$model->domains = 'new.company.admintrix.com';
			$model->language = 'en';
			$model->supportEmail = 'support@new.company.admintrix.com';
			$model->PaymentCash = 1;
			$model->save();
			$new_prefix = $model->id.'_';
			$s = strlen($prefix);
			$result = Yii::app()->db->createCommand('SHOW TABLES;')->queryAll();
			foreach($result as $item){
				if(strpos($item[key($item)],$prefix)===0) $list[] = substr($item[key($item)],$s);
			}
			foreach($list as $table){
				$sql = 'CREATE TABLE `'.$new_prefix.$table.'` LIKE `'.$prefix.$table.'`;';
				Yii::app()->db->createCommand($sql)->execute();
				if (in_array($table,$copy_data_list)) {
					$sql = 'INSERT `'.$new_prefix.$table.'` SELECT * FROM `'.$prefix.$table.'`;';
					Yii::app()->db->createCommand($sql)->execute();
				}
			}
			/*
			$sql = 'CREATE TABLE `'.$new_prefix.'Users'.'` LIKE `'.$prefix.'Users'.'`;';
			Yii::app()->db->createCommand($sql)->execute();
			$sql = 'CREATE TABLE `'.$new_prefix.'AuthAssignment'.'` LIKE `'.$prefix.'AuthAssignment'.'`;';
			Yii::app()->db->createCommand($sql)->execute();
			*/
			$sql = 'INSERT INTO `'.$new_prefix.'Users` (`username`,`password`,`email`,`superuser`,`status`) VALUES ("admin","'.UserModule::encrypting('admin').'","admin@new.company.admintrix.com",1,1);';
			Yii::app()->db->createCommand($sql)->execute();
			$sql = 'INSERT INTO `'.$new_prefix.'AuthAssignment` (`itemname`,`userid`) VALUES ("Admin",1);';
			Yii::app()->db->createCommand($sql)->execute();
			
			$this->redirect(array('edit','id'=>$model->id));
		}
		$companies = Company::getList();
		//Yii::app()->theme = 'admin';
		$this->render('create',array(
			'companies'=>$companies,
		));
	}
	
	// функция обратного вызова
	/*public static function next_year($matches)  {
		// как обычно: $matches[0] -  полное вхождение шаблона
		// $matches[1] - вхождение первой подмаски,
		// заключенной в круглые скобки, и так далее...
		echo 'matches 0: '.$matches[0].'<br>';
		echo 'matches 1: '.$matches[2].'<br>';
		return $matches[1].'?'.$matches[3];
	}*/

	public function actionSql() {
		if(isset($_POST['code']) && $_POST['code']) {
			$sql_input = $_POST['code'];
			preg_match_all(
              '/.+;\r\n/sU',
              $sql_input,
			  $out,
			  PREG_SET_ORDER);
			foreach($out as $command){
				$echo .= '<br>Command:<br>';
				$cmd = $command[0];
				foreach(Company::getList() as $key=>$company){
					$cur_cmd = preg_replace('/[0-9]+_/',$key.'_',$cmd);
					$sql .= $cur_cmd."\n";
					$echo .= $cur_cmd."<br>";
					//$sql_mass[] = $cur_cmd;
				}
			}
			try {
				$rows = Yii::app()->db->createCommand($sql)->execute();
				$echo = 'Success: '.$rows.' rows...<br>'.$echo;
			} catch (Exception $e) {
				$echo = 'Error!<br>'.$echo.'<br>'.$e;
			}
			
		}
	
		$this->render('sql',array(
			'echo'=>$echo,
		));
	}
}
?>