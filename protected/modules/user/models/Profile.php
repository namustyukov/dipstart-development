<?php

class Profile extends CActiveRecord
{
	/**
	 * The followings are the available columns in table 'profiles':
	 * @var integer $user_id
	 * @var boolean $regMode
	 */
	public $regMode = false;
	public $regType = 'Author';
	private $_model;
	private $_modelReg;
	private $_rules = array();
	
	public $_hours = null;
	public $_minutes = null;

	// первичная модель
	private $_modelSave;

	//public static $table_prefix;
	
	public function tableName() {
		return Company::getId().'_'.'Profiles'; //Yii::app()->getModule('user')->tableProfiles;
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	public function tableName()
	{
		return Yii::app()->getModule('user')->tableProfiles;
	}*/

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		if (!$this->_rules) {
			$required = array();
			$numerical = array();
			$float = array();
			$decimal = array();
			$rules = array();
			
			array_push($numerical,'mailing_for_executors');

			$model=$this->getFields();

			foreach ($model as $field) {
				$field_rule = array();
				if ($field->required==ProfileField::REQUIRED_YES_NOT_SHOW_REG||$field->required==ProfileField::REQUIRED_YES_SHOW_REG)
					array_push($required,$field->varname);
				if ($field->field_type=='FLOAT')
					array_push($float,$field->varname);
				if ($field->field_type=='DECIMAL')
					array_push($decimal,$field->varname);
				if ($field->field_type=='INTEGER')
					array_push($numerical,$field->varname);
				if ($field->field_type=='VARCHAR'||$field->field_type=='TEXT'||$field->field_type=='LIST') {
					$field_rule = array($field->varname, 'length', 'max'=>(($field->field_type=='TEXT' || $field->field_type=='LIST')?65535:$field->field_size), 'min' => $field->field_size_min);
					if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
					array_push($rules,$field_rule);
				}
				if ($field->other_validator) {
					if (strpos($field->other_validator,'{')===0) {
						$validator = (array)CJavaScript::jsonDecode($field->other_validator);
						foreach ($validator as $name=>$val) {
							$field_rule = array($field->varname, $name);
							$field_rule = array_merge($field_rule,(array)$validator[$name]);
							if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
							array_push($rules,$field_rule);
						}
					} else {
						$field_rule = array($field->varname, $field->other_validator);
						if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
						array_push($rules,$field_rule);
					}
				} elseif ($field->field_type=='DATE') {
					$field_rule = array($field->varname, 'type', 'type' => 'date', 'dateFormat' => 'yyyy-mm-dd', 'allowEmpty'=>true);
					if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
					array_push($rules,$field_rule);
				}
				if ($field->match) {
					$field_rule = array($field->varname, 'match', 'pattern' => $field->match);
					if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
					array_push($rules,$field_rule);
				}
				if ($field->range) {
					$field_rule = array($field->varname, 'in', 'range' => self::rangeRules($field->range));
					if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
					array_push($rules,$field_rule);
				}
			}

			array_push($rules,array(implode(',',$required), 'required'));
			array_push($rules,array(implode(',',$numerical), 'numerical', 'integerOnly'=>true));
			array_push($rules,array(implode(',',$float), 'type', 'type'=>'float'));
			array_push($rules,array(implode(',',$decimal), 'match', 'pattern' => '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/'));
			array_push($rules,array('hours, minutes', 'safe'));
			$this->_rules = $rules;
		}
		return $this->_rules;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		$relations = array(
			'user'=>array(self::HAS_ONE, 'User', 'id'),
			//'categories'=>array(self::HAS_MANY, 'Categories', array('id'=>'discipline')),
			'AuthAssignment' => array(self::HAS_ONE, 'AuthAssignment', 'userid'),
		);
		if (isset(Yii::app()->getModule('user')->profileRelations))
			$relations = array_merge($relations,Yii::app()->getModule('user')->profileRelations);
		return $relations;
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'user_id' => UserModule::t('User ID'),
			'rating' => UserModule::t('Rating'),
			'mailing_for_executors' => UserModule::t('Recive new projects notifications'),
			'notification' => UserModule::t('Receive notification of the occurrence of terms'),
			'notification_time' => UserModule::t('Time notification'),
		);
		$model=$this->getFields();

		foreach ($model as $field)
			$labels[$field->varname] = ((Yii::app()->getModule('user')->fieldsMessage)?UserModule::t($field->title,array(),Yii::app()->getModule('user')->fieldsMessage):UserModule::t($field->title));

		return $labels;
	}

	private function rangeRules($str) {
		$rules = explode(';',$str);
		for ($i=0;$i<count($rules);$i++)
			$rules[$i] = current(explode("==",$rules[$i]));
		return $rules;
	}

	static public function range($str,$fieldValue=NULL) {
		$rules = explode(';',$str);
		$array = array();
		for ($i=0;$i<count($rules);$i++) {
			$item = explode("==",$rules[$i]);
			if (isset($item[0])) $array[$item[0]] = ((isset($item[1]))?$item[1]:$item[0]);
		}
		if (isset($fieldValue))
			if (isset($array[$fieldValue])) return $array[$fieldValue]; else return '';
		else
			return $array;
	}

	public function widgetAttributes() {
		$data = array();
		$model=$this->getFields();

		foreach ($model as $field) {
			if ($field->widget) $data[$field->varname]=$field->widget;
		}
		return $data;
	}

	public function widgetParams($fieldName) {
		$data = array();
		$model=$this->getFields();

		foreach ($model as $field) {
			if ($field->widget) $data[$field->varname]=$field->widgetparams;
		}
		return $data[$fieldName];
	}

	public function getFields() {
		if ($this->regMode) {
			if (!$this->_modelReg){
				$criteria = new CDbCriteria();
                $criteria->order = 'position';
				if ($this->regType == 'Author'){
					$criteria->addInCondition('visible',array(2,3));
					$this->_modelReg=ProfileField::model()->findAll($criteria);
				}elseif ($this->regType == 'Customer'){
					$criteria->addInCondition('visible',array(1,3));
					$this->_modelReg=ProfileField::model()->findAll($criteria);
				}
		   }
			return $this->_modelReg;
		} else {
			if (!$this->_model) {
				$criteria = new CDbCriteria();
                $criteria->order = 'position';
				if (User::model()->isCustomer()) {
					$criteria->addInCondition('visible',array(1,3));
					$this->_model=ProfileField::model()->findAll($criteria);
				} elseif (User::model()->isAuthor()) {
					$criteria->addInCondition('visible',array(2,3));
					$this->_model=ProfileField::model()->findAll($criteria);
				} elseif (User::model()->isManager()) {
					$this->_model=ProfileField::model()->findAll();
				} else {
					$this->_model=ProfileField::model()->forAll()->findAll();
				}
			}
			return $this->_model;
		}
	}
	
	protected function beforeValidate() {
		$model = $this->getFields();

		foreach ($model as $field) {
			if ($field->field_type=="LIST"){
				$fname = $field->varname;
				if ($this->$fname && is_array($this->$fname)) {
				   $this->$fname = implode(",", $this->$fname);
				}
			}
		}
		
		if ($this->notification == '1') {
			/*$validator = new CRequiredValidator;
			$validator->attributes = array('notification_time');
			$this->validatorList->add($validator);*/
			
			//if (!empty($this->hours) && !empty($this->minutes)) $this->notification_time = $this->hours . ':' . $this->minutes;
			//else $this->notification_time = '';
			
			$this->notification_time = $this->hours . ':' . $this->minutes;
		}
		else $this->notification_time = '';
		return parent::beforeValidate();
	}
	public function beforeSave(){
		if(parent::beforeSave()){
			$model=$this->getFields();
			foreach ($model as $field) {
				if ($field->field_type=="LIST"){
					$fname = $field->varname;
					if ($this->$fname && is_array($this->$fname)) {
					   $this->$fname = implode(",", $this->$fname);
					}
				}
			}
			/*if (isset($this->discipline) && is_array($this->discipline)) {
			   $this->discipline = implode(",", $this->discipline);
			}
			if (isset($this->job_type) && is_array($this->job_type)) {
			   $this->job_type = implode(",", $this->job_type);
			}*/
			// запрашиваем модерацию перед сохранением данных профиля
//			if((!$this->isNewRecord) && (!Yii::app()->user->checkAccess('Manager'))) {
//				$this->getChanges();
//				return !parent::beforeSave();
//			}
            return true;
		} else {
            return false;
        }
	}
	public function afterFind() {
		$this->_modelSave = $this->attributes;
		return parent::afterFind();
	}
	
	public function getPayNumber($payType, $user) {
		$data = Yii::app()->db->createCommand("SELECT * FROM `" . self::tableName() . "` WHERE user_id = {$user}")->queryRow();
		return $payType != 'cash' ? $data[$payType] : '';
	}
	
	/*
	 * список изменений для записи
	 */
	protected function getChanges() {
		$res = false;
	
		if (!empty($this->_modelSave)) {
			foreach ($this->_modelSave as $key => $value) {
				if ($this->$key != $value) {
					UpdateProfile::addRecord($key,$value,$this->$key);
					$res = true;
				}
			}
			if($res) {
				// можно внести в конфигу
				Yii::import('application.modules.project.components.EventHelper');
				EventHelper::updateProfile();
			}
		}
		return $res;
	}
    
    /*public function behaviors()
    {
        return [
            'ModerateBehavior' => [
                'class' => 'ModerateBehavior'
            ]
        ];
    }*/
	
	public function getTime($type) {
		$time = array('0' => '0');
		if ($type == 'hours') for ($i = 1; $i <= 23; $i++) $time[$i] = $i;
		else if ($type == 'minutes') for ($i = 1; $i <= 59; $i++) $time[$i] = $i;
		return $time;
	}
	
	public function getHours()
	{
		if ($this->_hours === null) $this->_hours = explode(':', $this->notification_time)[0];
		return $this->_hours;
	}
	public function setHours($value)
	{
		$this->_hours = $value;
	}
	
	public function getMinutes()
	{
		if ($this->_minutes === null) $this->_minutes = explode(':', $this->notification_time)[1];
		return $this->_minutes;
	}
	public function setMinutes($value)
	{
		$this->_minutes = $value;
	}
}
