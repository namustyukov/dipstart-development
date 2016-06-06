<?php

/**
 * This is the model class for table "ProjectChanges".
 *
 * The followings are the available columns in table 'ProjectChanges':
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $project_id
 * @property string  $file
 * @property string  $comment
 * @property string  $date_create
 * @property string  $date_update
 * @property string  $date_moderate
 * @property integer     $moderate
 */
Yii::import('application.helpers.STranslate');

class ProjectChanges extends CActiveRecord {

    public static $file_path;/* = 'uploads/changes_documents';*/
    //public $old_file;
    public $fileupload;

	public function tableName() {
		return Company::getId().'_ProjectChanges';
	}

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {

        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, project_id, date_create', 'required'),
            array('user_id, project_id', 'numerical', 'integerOnly' => true),
            array('user_id, project_id, date_create', 'unsafe'),
            array('user_id, project_id, date_create, moderate', 'safe', 'on' => 'add'),
            array('comment', 'safe', 'on' => 'edit'),
            array('file', 'length', 'max' => 350),
            array('file', 'validateOnEmptyFileComment'),
            array('fileupload', 'file', 'types' => 'docx, doc, pdf, jpg, jpeg, png, xls, xlsx, txt, zip, rar', 'allowEmpty' => true, 'maxSize' => Tools::maxFileSize(), 'tooLarge' => 'File has to be smaller than 200MB'),
            array('comment', 'length', 'max' => 450),
            array('moderate', 'length', 'max' => 45),
            array('date_update, date_moderate', 'safe'),
            array('moderate', 'safe', 'on' => 'approve'),
            array('id, user_id, project_id, file, comment, date_create, date_update, date_moderate', 'unsafe', 'on' => 'approve'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, user_id, project_id, file, comment, date_create, date_update, date_moderate, moderate', 'safe', 'on' => 'search'),
        );
    }

    public function validateOnEmptyFileComment($field, $params) {

        $labels = $this->attributeLabels();
        if (!$this->fileupload & !$this->comment) {
            $this->addError($field, $labels['file'] . ' и ' . $labels['comment'] . ' оба не могут быть пустыми!');

        }
    }

    /**
     * @return array relational rules.
     */
    public function relations() {

        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::HAS_ONE, 'User', 'user_id'),
            'project' => array(self::HAS_ONE, 'Zakaz', 'project_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {

        return array(
            'id' => ProjectModule::t('ID'),
            'user_id' => ProjectModule::t('User'),
            'project_id' => ProjectModule::t('Project'),
            'file' => ProjectModule::t('File'),
            'fileupload' => ProjectModule::t('Attach file'),
            'comment' => ProjectModule::t('Comment'),
            'date_create' => ProjectModule::t('Date Create'),
            'date_update' => ProjectModule::t('Date Update'),
            'date_moderate' => ProjectModule::t('Date Moderate'),
            'moderate' => ProjectModule::t('Approved'),
        );
    }

    public function scopes() {

        return array(
            'default' => array(
                'order' => 'date_create DESC'
            )

        );
    }

    public function scopeForRole($role) {

        $condition = array();
        if ($role == 'Author') {
            $condition = array(
                'condition' => 'moderator=1'
            );
        }
        $this->getDbCriteria()->mergeWith($condition);

        return $this;
    }

    protected function beforeValidate() {

        if ($this->isNewRecord) {
            $this->date_create = date('Y-m-d H:i:s');
            if (!Yii::app()->user->isGuest) {
                $this->user_id = Yii::app()->user->id;
            }
        }

        $result = parent::beforeValidate();

        return $result;


    }

    public function beforeSave() {
        /*if ($this->fileupload instanceof CUploadedFile) {
            $mainName = $newName = STranslate::transliter($this->fileupload->getName());
            $i = 1;
            while (file_exists($this->getPathDirStoredFile() . '/' . $newName)) {
                $parts = explode('.', $mainName);
                $countParts = count($parts);
                if ($countParts > 1) {
                    unset($parts[$countParts - 1]);
                    $newName = implode($parts) . '(' . $i . ').' . $this->fileupload->extensionName;
                } else {

                    $newName = '(' . $i . ')' . $mainName;
                }

                $i++;
            }
			if (!file_exists($this->getPathDirStoredFile())) mkdir($this->getPathDirStoredFile(),0755,true);
            $this->fileupload->saveAs($this->getPathDirStoredFile() . '/' . $newName);
			
            $this->file = $newName;
            if (!empty($this->old_file)) {
                $delete = $this->getPathDirStoredFile() . '/' . $this->old_file;
                if (file_exists($delete)) {
                    unlink($delete);
                }
            }
        }
        if (empty($this->file) && !empty($this->old_file)) {
            $this->file = $this->old_file;
        }*/
		$this->file = Tools::saveUploadedFile($this->fileupload, $this->getPathDirStoredFile(), $this->file);
        return parent::beforeSave();
    }

    /*public function afterFind() {

        parent::afterFind();
        $this->old_file = $this->file;
    }*/

    public function afterDelete() {

        $this->deleteFile();

        parent::afterDelete();
    }

    public function getListChanges($project_id) {

        $result = Yii::app()->db->createCommand()
                                ->select('CONCAT("/' . self::$file_path . '/",file)  as `file`, file as `filename`, comment, id, moderate, date_create')
                                ->from($this->tableName())
                                ->where('project_id =' . (int)$project_id . (User::model()->isManager() || User::model()->isAdmin() ? '' : (' AND (user_id = '.Yii::app()->user->id.' OR moderate=1)' )))
                                ->queryAll();

        return CHtml::encodeArray($result);
    }

    public function getItem($id) {

        $result = Yii::app()->db->createCommand()
                                ->select('CONCAT("/' . self::$file_path . '/",file)  as `file`, file as `filename`, comment, id, moderate, date_create')
                                ->from($this->tableName())
                                ->where('id =' . (int)$id)
                                ->queryRow();

        return $result;
    }

    /**
     * Deletes the file
     */
    public function deleteFile() {

        return @unlink($this->getFilePath());

    }

    /**
     * Returns path to file
     *
     * @return string
     */
    public function getFilePath() {

        return $this->getPathDirStoredFile() . '/' . $this->file;
    }

    /**
     * Returns url to file
     *
     * @return string
     */
    public function getFileUrl() {

        return Yii::app()->basePath . '/' . self::$file_path . '/' . $this->file;
    }

    static public function approveAllowed() {
        return (User::model()->isManager() || User::model()->isAdmin());
    }

    /**
     * Returns path to dir where stored files
     *
     * @return string
     */
    public function getPathDirStoredFile() {

        return Yii::getPathOfAlias('webroot') . '/' . self::$file_path;
    }

    /**
     * Returns true if the changes were approved
     *
     * @return bool
     */
    public function isModerate() {

        return (boolean)$this->moderate;
    }

    /**
     * Returns true if current user can add changes, else false
     *
     * @return bool
     */
    public function isAllowedAdd() {

        if (User::model()->isManager() || User::model()->isAdmin() || User::model()->isCorrector()) {
            return true;
        }
        $project = Zakaz::model()->findByPk($this->project_id);
        if (User::model()->isAuthor() || !$project) {
            return false;
        }
        if (!Yii::app()->user->isGuest && Yii::app()->user->id == $project->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Generate name
     *
     * @return string
     */
    /*public function generateFileName() {

        $name = '';
        $count = rand(5, 8);
        for ($i = 0; $i < $count; $i++) {
            $name .= chr(rand(97, 122)) . chr(rand(48, 57)) . chr(rand(65, 90));
        }

        return $name;

    }*/

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search() {

        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('project_id', $this->project_id);
        $criteria->compare('file', $this->file, true);
        $criteria->compare('comment', $this->comment, true);
        $criteria->compare('date_create', $this->date_create, true);
        $criteria->compare('date_update', $this->date_update, true);
        $criteria->compare('date_moderate', $this->date_moderate, true);
        $criteria->compare('moderate', $this->moderate, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }


    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     *
     * @param string $className active record class name.
     *
     * @return ProjectChanges the static model class
     */
    public static function model($className = __CLASS__) {

        return parent::model($className);
    }
}