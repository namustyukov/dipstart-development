<?php

class ChangesController extends Controller {
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */

    /**
     * @return array action filters
     */
    /*public function filters() {

        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }*/


    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     *
     * @return array access control rules
     */
    /*public function accessRules() {

        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                  'actions' => array('add', 'edit', 'list', 'delete', 'item'),
                  'users' => array('@'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                  'actions' => array('approve'),
                  'expression' => array('ProjectChanges', 'approveAllowed')
            ),
            array('deny',  // deny all users
                  'users' => array('*'),
            ),
        );
    }*/
    public function init(){
        parent::init();
        header("Content-type: application/json");
    }

    public function actionList($project)
    {

        /**
         * @var $projects ProjectChanges[]
         */
        if (!Yii::app()->request->isAjaxRequest) {
            return false;
        }

        $projects = ProjectChanges::model()->getListChanges((int)$project);
        foreach ($projects as $data) {?>
            <div id="list-changes-block" class="list-changes-block">
                <div class="list-changes-filename">
                    <a href="<?php echo $data['file']; ?>"><?php echo $data['filename']; ?></a>
                </div>
                <div class="comment list-changes-comment">
                    <?php echo $data['comment']; ?>
                </div>
            </div>
            <?php if (ProjectChanges::approveAllowed()) { ?>
                <div class="row">
                    <?= ProjectModule::t('Moderation'); ?>
                    <?php echo CHtml::dropDownList(
                        'moderate',
                        $data['moderate'],
                        array('1' => ProjectModule::t('Approved'), '0' => ProjectModule::t('Not approved')),
                        array('onchange' => '$.post(\''.Yii::app()->createUrl('/project/changes/approve?id='.$data['id']).'\',{moderate:'.$data['moderate'].'});'));
                    ?>
                </div>
            <?php } ?>
        <?php }
        Yii::app()->end();
    }

    public function actionItem($id) {

        /**
         * @var $projects ProjectChanges[]
         */
        if (!Yii::app()->request->isAjaxRequest) {
            return false;
        }

        if (Yii::app()->request->getPost('text')!=null) {
            $project = ProjectChanges::model()->findByPk((int)$id);
            $project->comment = Yii::app()->request->getPost('text');
            $project->save();
        }
        echo CJSON::encode(array('data' => ProjectChanges::model()->getItem((int)$id)));
        Yii::app()->end();

    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionAdd($project) {
	
        if (!Yii::app()->request->isAjaxRequest) {
            return false;
        }

		$c_id = Company::getId();
		if ($c_id) {
			ProjectChanges::$file_path = 'uploads/c'.$c_id.'/changes_documents';
		} else {
			ProjectChanges::$file_path = 'uploads/changes_documents';
		}
		
        $model = new ProjectChanges();
        $model->scenario = 'add';
        $model->project_id = (int)$project;
		
        if (isset($_POST['ProjectChanges'])) {
            $model->attributes = $_POST['ProjectChanges'];
            $model->fileupload = CUploadedFile::getInstance($model, 'fileupload');
			
            if (!empty($model->fileupload)) {
                //$model->file = 'no';
            } else {
				echo  CJSON::encode(array('error' => array('text' => 'file-not-uploaded')));
				Yii::app()->end();
			}
            if (ProjectChanges::approveAllowed()) {
                $model->moderate = 1;
            } else {
                $model->moderate = 0;
            }
			
            if (!$model->validate()) {
                echo CJSON::encode(array('error' => array('text' =>print_r($model->errors, true))));
                Yii::app()->end();
            }
			
			//echo CJSON::encode(array('error' => array('text' => 'Ups!')));
			//Yii::app()->end();
			
            try {
                if ($model->isAllowedAdd() && $model->save(false)) {
                    if (!(User::model()->isManager() || User::model()->isAdmin())) EventHelper::addChanges($model->project_id);
                    if ($model->moderate == 1)
                    {
                        $orderModel = Zakaz::model()->findByPk($model->project_id);
                        $orderModel->setExecutorEvents(4);
                    }
                    echo CJSON::encode(array('success' => true));
                    Yii::app()->end();
                } else {
                    echo CJSON::encode(array('error' => array('text' => 'Вы не можете внести правки к этому проекту!')));
                    Yii::app()->end();
                }
            } catch (CException $e) {
                echo YII_DEBUG ? CJSON::encode(array('error' => array('text' => $e->getMessage()))) :
                    CJSON::encode(array('error' => array('text' => 'Ошибка добавления!')));
                Yii::app()->end();
            }
        }

    }

    public function actionApprove($id) {

        if (!Yii::app()->request->isAjaxRequest) {
            return false;
        }
        $model = $this->loadModel($id);
        $model->scenario = 'approve';
        if (!$model->isAllowedAdd()) {
            echo CJSON::encode(array('error' => array('text' => array(ProjectModule::t('You are not allowed to change!')))));
            Yii::app()->end();
        }
        if (isset($_REQUEST['moderate'])) {
            $model->moderate = !$model->isModerate() ? '1' : '0';
            $model->date_update = date('Y-m-d H:i:s');
            if (ProjectChanges::approveAllowed()) {
                $model->date_moderate = date('Y-m-d H:i:s');
				if($model->moderate) {
					$orderId = $model->project_id;
					$order = Zakaz::model()->findByPk($orderId);
					$user = User::model()->findByPk($order->executor);
					$type_id = Emails::TYPE_23;
					$email = new Emails;
					$rec   = Templates::model()->findAll("`type_id`='$type_id'");
					$email->name = $user->full_name;
					if (strlen($email->name) < 2) $email->name = $user->username;
					$email->num_order = $orderId;
					$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
					$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $type_id);
				}
            }

            if ($model->save(false)) {
                if ($model->moderate == 1 && $order)
                    $order->setExecutorEvents(4);
                echo CJSON::encode(array('success' => true, 'approve' => ($model->isModerate() ? 'true' : 'false')));
                Yii::app()->end();
            }
        }
        echo CJSON::encode(array('error' => array('text' => array('Данные не переданы!'))));
        Yii::app()->end();
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id the ID of the model to be updated
     */
    public function actionEdit($id) {

        if (!Yii::app()->request->isAjaxRequest) {
            return false;
        }
        $model = $this->loadModel($id);
        $model->scenario = 'edit';
        if (!$model->isAllowedAdd()) {
            echo CJSON::encode(array('error' => array('text' => array(ProjectModule::t('You are not allowed to change!')))));
            Yii::app()->end();
        }
        if (isset($_POST['ProjectChanges'])) {
            $model->date_update = date('Y-m-d H:i:s');
            $model->attributes = $_POST['ProjectChanges'];
            $model->fileupload = CUploadedFile::getInstance($model, 'fileupload');
            if (ProjectChanges::approveAllowed()) {
                $model->date_moderate = date('Y-m-d H:i:s');
                $model->moderate = 1;
            } else {
                $model->moderate = 0;
            }
            if (!$model->validate()) {
                echo CJSON::encode(array('error' => CJSON::decode(CActiveForm::validate($model))));
                Yii::app()->end();
            }
            if ($model->save(false)) {
                echo CJSON::encode(array('success' => true));
                Yii::app()->end();
            }
        }
        echo CJSON::encode(array('error' => array('text' => array('Данные не переданы!'))));
        Yii::app()->end();
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     *
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {

        $changes = $this->loadModel($id);

        if ($changes->isAllowedAdd()) {
            $changes->delete();
        } else {
            //TODO сделать оповещение пользователя, о том что ему запрещено удалять доработки
        }

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        } else {
            echo CJSON::encode(array('success' => true));
            Yii::app()->end();
        }
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     *
     * @param integer $id the ID of the model to be loaded
     *
     * @return ProjectChanges the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {

        $model = ProjectChanges::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, Yii::t('site','The requested page does not exist.'));
        }

        return $model;
    }


}