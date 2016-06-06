<?php


class ZakazPartsController extends Controller {
	/* actions:
	Manager: (manager.js)
		apiCreate
		apiDelete
		apiEditPart
		apiApprove (file)
		apiDeleteFile
	Author and Customer: (chat.js)
		status
	Author (widget view)
		upload
	*/
    
    protected $_request;
    protected $_response;
    protected $_file_data;
    protected $result;
    
    /*Вызов методов для работы с json*/
    protected function _prepairJson() {
        $this->_request = Yii::app()->jsonRequest;
        $this->_response = new JsonHttpResponse();
    }
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	//public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	/*public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}*/

	/**
	 * Performs the AJAX validation.
	 * @param ZakazParts $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='zakaz-parts-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function folder() {
		return ZakazPartsFiles::model()->folder();
	}

	/* Получение списка частей для заказа по ИД*/
	/*public function actionApiGetAll() {
		// --- campaign
		$folder = $this->folder();
		$this->_prepairJson();
		$zakazId = $this->_request->getParam('orderId');
		if (User::model()->isAdmin() || User::model()->isManager()) {
			$models = ZakazParts::model()->findAll('proj_id = :PROJ_ID',
				array(":PROJ_ID"=>$zakazId)
			);
			$parts = array();
			foreach ($models as $model) {
				$part['id'] = $model->id;
				$part['proj_id'] = $model->proj_id;
				$part['title'] = $model->title;
				$part['date'] = Yii::app()->dateFormatter->formatDateTime($model->date, 'medium' ,'');
				$part['author_id'] = $model->author_id;
				$part['author'] = $model->getRelated('author')->username;
				$part['show'] = $model->show;
				$part['comment'] = $model->comment;
				$part['file'] = ZakazPartsFiles::model()->findAll('part_id = :PART_ID',
					array("PART_ID"=>$model->id)
				);
				$for_moderation = array_diff(scandir(YiiBase::getPathOfAlias('webroot').$folder.'temp'), array('..', '.'));
				foreach ($for_moderation as $k=>$v)
					if(preg_match('/_'.$model->id.'./i',$v)){
						$for_moderation[$k]=array(
							'comment'=>0,
							'file_name'=>0,
							'id'=>0,
							'orig_name'=>preg_replace('/_'.$model->id.'/i','',$v),
							'part_id'=>$model->id,
							'for_approved'=>'Must approved',
						);
					} else unset($for_moderation[$k]);
				$part['file'] = array_merge($part['file'],$for_moderation);
				$parts[] = $part;
			}
			$this->_response->setData(array(
				'parts'=>$parts
			));
			$this->_response->send();
		} elseif (User::model()->isCustomer() || User::model()->isAuthor()) {
			$model = ZakazParts::model()->findAll('proj_id = :PROJ_ID AND `show` IN (1'.(User::model()->isAuthor()?',0)':')'),
				array(':PROJ_ID'=>$zakazId)
			);
			$parts = array();
			foreach ($model as $k => $part) {
				foreach ($part as $kk => $vv) {
					$parts[$k][$kk]=$vv;
				}
				$parts[$k]['file'] = $part->getRelated('files');
				$parts[$k]['author'] = $part->getRelated('author')->username;
			}
			$this->_response->setData(array('parts'=>$parts));
			$this->_response->send();
		}
	}*/
	public function actionApiApprove() { /* Approve files in parts */
		$this->_prepairJson();
		$fileid = $this->_request->getParam('data')['id'];
		$file = ZakazPartsFiles::model()->findByPk($fileid);
		$file->approved = $file->approved ? 0 : 1;
		$file->save();
		$this->_response->setData(array(
			'success' => 1
		));
		$this->_response->send();
	}
	public function actionApiEditPart() {
		$folder = $this->folder();
		$this->_prepairJson();
		$partId = $this->_request->getParam('id');
		$model = ZakazParts::model()->findByPk($partId);
		$beforeDate = $model->dbdate;
		foreach ($this->_request->_params as $par=>$val)
			$model->$par =$val;
		$afterDate = $model->dbdate;
		/*if ($this->_request->isParam('files')) {
			$files = $this->_request->getParam('files');
			$path = $folder.$partId.'/';
			$this->checkDir($path);
			foreach($files as $file) {
				$list = explode('.', $file);
				$newName = $this->getGuid();
				$filePath = $_SERVER['DOCUMENT_ROOT'].$folder.'temp/'.$file;
				$fileNewPath = $_SERVER['DOCUMENT_ROOT'].$folder.$partId.'/'.$newName.".".$list['1'];
				$probe = rename($filePath, $fileNewPath);
				$fileModel = new ZakazPartsFiles();
				$fileModel->part_id = $model->id;
				$fileModel->orig_name = $file;
				$fileModel->file_name = $newName.".".$list['1'];
				$fileModel->comment = '';
				$fileModel->save();
			}
		}*/

		if ($beforeDate != $afterDate)
		{
			$order = Zakaz::model()->findByPk($model->proj_id);
			$order->setExecutorEvents(3);
		}

		$this->_response->setData(array(
			'result' => $model->save()
		));
		$this->_response->send();
	}
	
	/*private function checkDir($path) {
		if (!file_exists($path)){
			mkdir($path, 0755, true);
		}
	}
	
	protected function getGuid(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12);// "}"
			return $uuid;
		}
	}*/
	
	/*public function actionApiEditFilesComment() {
		$this->_prepairJson();
		$fileid = $this->_request->getParam('id');
		$file = new ZakazPartsFiles;
		$id = $file->changeComment($fileid, $this->_request->getParam('comment'));
		$this->_response->setData(array(
			'id' => $id
		));
		$this->_response->send();
	}*/
	
	/*public function actionApiChangeIsShowed() {
		$this->_prepairJson();
		$partId = $this->_request->getParam('id');
		$part = ZakazParts::model()->findByPk($partId);
		if ($part->show == 1) {
			$part->show = 0;
		} else {
			$part->show = 1;
		}
		$part->save();
		$this->_response->setData(array(
			'result' => true
		));
		$this->_response->send();
	}*/
	
	public function actionApiDeleteFile() {
		$this->_prepairJson();
		$fileid = $this->_request->getParam('id');
		$file = ZakazPartsFiles::model()->findByPk($fileid);
		$file->delete();
		$this->_response->setData(array(
			'success' => 1
		));
		$this->_response->send();
	}
	
	/*Создание новой части на основе имени и ИД-заказа*/
	public function actionApiCreate() {
		$this->_prepairJson();
		$zakazId = $this->_request->getParam('orderId');
		$zakaz = Zakaz::model()->findByPk($zakazId);
		$model = new ZakazParts;
		$model->proj_id = $zakaz->id;
		$model->title = $this->_request->getParam('name');
		$model->author_id = $zakaz->executor;
		if ( $model->save() ) {
			$this->_response-> setData(array(
				'result'=>$model->id
			));
			$this->_response->send();
			
			
		} else {
			$this->_response->setData(array(
				'result'=>false
			));
			$this->_response->send();
		}
	}
	
	/*Удаление части по ИД*/
	public function actionApiDelete() {
		$this->_prepairJson();
		$id = $this->_request->getParam('id');
		$part =  ZakazParts::model()->findByPk($id);
		
		if ($part->delete()) {
			$this->_response->setData(array(
				'result'=>true
			));
			$this->_response->send();
		} else {
			$this->_response->setData(array(
				'result'=>false
			));
			$this->_response->send();
		}
	}
	
	/*Получение данных части по ИД для дальнейшего редактирования*/
	public function actionApiGetPart() {
		$this->_prepairJson();
		$id = $this->_request->getParam('id');
		$model = ZakazParts::model()->findByPk($id);
		$files = ZakazPartsFiles::model()->findAll('part_id = :PART_ID',
					array("PART_ID"=>$model->id)
				);
		$this->_response->setData(array(
				'part' => $model,
				'files' => $files
			));
		$this->_response->send();
	}
	
	public function actionUpload() {
		$this->_prepairJson();
		$folder = $_SERVER['DOCUMENT_ROOT'].$this->folder().$_GET['id'].'/';
		
		$fileModel = new ZakazPartsFiles();
		$fileModel->part_id = $_GET['id'];
		$fileModel->orig_name = $_GET['qqfile'];

		$this->result = Tools::uploadMaterials($folder, false);

		if ($this->result['success']) {
			$fileModel->file_name = $this->result['fileName'];
			$part = ZakazParts::model()->findByPk($_GET['id']);
			if (User::model()->isManager()) {
				$fileModel->approved = 1;
			} else {
				EventHelper::newFileInStage($_GET['proj_id'], $part->title);
			}
			$fileModel->save();
		}
		//$this->result['html']='=)';//'<li>!!!<a href="' . $this->result['file_name'] . '" id="parts_file">' . $_GET['qqfile'] . '</a></li>';
		//$this->result = array('error' => $this->result['error']);
		$this->_response->setData($this->result);
		$this->_response->send();
	}
	
    public function actionStatus() {
		$status_id	= Yii::app()->request->getPost('status_id');
		$id			= Yii::app()->request->getPost('id');
		if(User::model()->isAuthor() && $status_id=='+1' && $id){
			$stage = ZakazParts::model()->findByPk($id);
			if(User::model()->isExecutor($stage->proj_id) && $stage->status_id == 1) {
				$stage->status_id = 2;
				$stage->save();
				echo $stage->status->status;
				EventHelper::stageDoneByExecutor($stage->proj_id, $stage->title);
			}else{
				echo 'Wrong base status';
			}
		}elseif(User::model()->isCustomer() && $status_id=='+1' && $id){
			$stage = ZakazParts::model()->findByPk($id);
			if(User::model()->isOwner($stage->proj_id) && $stage->status_id == 3) {
				$stage->status_id = 4;
				$stage->save();
				//echo $stage->status->status;
				echo ProjectModule::t('Approved by me');
				EventHelper::stageDoneByCustomer($stage->proj_id, $stage->title);
			}else{
				echo 'Wrong base status';
			}
		}elseif(User::model()->isManager() && $status_id && $id) {
			$orderId	= Yii::app()->request->getPost('orderId');
			$row	= array(
				'status_id'	=> $status_id,
			);
			$condition 	= array();
			$params		= array();
			ZakazParts::model()->updateByPk( $id, $row, $condition, $params);
			
			if ((int)$status_id == 3) {
				$parts = ZakazParts::model()->findAll("`proj_id` = '$orderId' AND `status_id` IN (0,1,2)");
				$order = Zakaz::model()->resetScope()->findByPk($orderId);
				$subject_order = $order->title;
				$user_id = $order->user_id;
				$user = User::model()->findByPk($user_id);

				$order->setCustomerEvents(2);

				$email = new Emails;
				if (count($parts) > 0)  $type_id = Emails::TYPE_14; else
										$type_id = Emails::TYPE_15;
										
				$rec   = Templates::model()->findAll("`type_id`='$type_id'");
				
				echo count($parts);
				
				$title = $rec[0]->title;
				$body  = $rec[0]->text;
				$email->name = $user->full_name;
				if (strlen($email->name) < 2) $email->name = $user->username;
				$email->num_order = $orderId;
		//		$model->date = date('Y-m-d H:i:s');
				$email->subject_order = $subject_order;
				$email->num_order = $orderId;
				$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
				$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $type_id);
			}
		}
    }
}
