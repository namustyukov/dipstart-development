<?php

/**
 * Created by stixlink.
 * E-mail: stixlink@gmail.com
 * Skype: stixlink
 * Date: 19.12.14
 * Time: 16:39
 */

/**
 * Class ChangesWidget
 *
 * @property ProjectChanges $changes
 * @property Zakaz          $project
 */
class ChangesWidget extends CWidget
{
	public $hints;
    public $changes;
    public $project;
    public $isCorrector;
    protected $userObj;

    public function init() {
		//echo 'project_id =' . (int)$this->project->id .' '. ((User::model()->isManager() || User::model()->isAdmin()) ? '' : (' AND (user_id = '.((Yii::app()->user->id)?Yii::app()->user->id:(-1)).' OR moderate=1)' )).'!!!';
        $this->userObj = User::model();
        $this->changes = new CArrayDataProvider(Yii::app()->db->createCommand()
            ->select('CONCAT("/' . ProjectChanges::$file_path . '/",file)  as `file`, file as `filename`, comment, id, moderate, date_create')
            ->from(ProjectChanges::model()->tableName())
            ->where('project_id =' . (int)$this->project->id . (User::model()->isManager() || User::model()->isAdmin() ? '' : (' AND (user_id = '.((Yii::app()->user->id)?Yii::app()->user->id:(-1)).' OR moderate=1)' )))
            ->queryAll(),
            array(
                'pagination' => false,
            ));
    }

    public function run() {
		//echo 'go'.count($this->changes->rawData);
        if (count($this->changes->rawData) > 0 || User::model()->isCustomer() || $this->isCorrector || User::model()->isManager() || User::model()->isAdmin()) {
            if(Yii::app()->user->isGuest || (User::model()->isAuthor() && !User::model()->isExecutor($this->project->id) && !$this->isCorrector)){
				$this->render('simple', array('changes' => $this->changes));
			}else{
				$this->render('default', array(
                    'changes' => $this->changes, 
                    'project' => $this->project, 
                    'user' => $this->userObj,
                    'isCorrector' => $this->isCorrector,
                ));
			}
		}
    }

}