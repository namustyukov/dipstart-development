<?php

/**
 * This is the model class for table "Projects".
 *
 * The followings are the available columns in table 'Projects':
 * @property string $id
 * @property string $user_id
 * @property integer $category_id
 * @property integer $job_id
 * @property string $title
 * @property string $text
 * @property string $date
 * @property string $max_exec_date
 * @property string $date_finish
 * @property integer $pages
 * @property string $add_demands
 * @property integer $status
 * @property integer $old_status
 * @property integer $is_active
 * @property string $executor
 * @property User $user
 * @property User $author
 * @property ProjectStatus $projectStatus
 * @property integer $uppercheckbox
 */
class Zakaz extends CActiveRecord {
	private $_model;
	private $_rules = array();
	
	public static $files_folder;

    /*private $_job_name;
    private $_cat_name;*/
    private $_status_name;
    private $date_finishstart;
    private $date_finishend;

    public $dateTimeIncomeFormat = 'yyyy-MM-dd HH:mm:ss';
    public $dateTimeOutcomeFormat = 'dd.MM.yyyy HH:mm';
    public $dateIncomeFormat = 'yyyy-MM-dd HH:mm:ss';
    public $dateOutcomeFormat = 'dd.MM.yyyy';
    
    public $unixtime = '';
    

    public function executorEventsArr() {
    	return array(
	    	1 => ProjectModule::t('Change in the ordering information'),
			2 => ProjectModule::t('Message in chat'),
			3 => ProjectModule::t('Changing the timing'),
			4 => ProjectModule::t('Added revision'),
	    );
    }

    public function customerEventsArr() {
    	return array(
			1 => ProjectModule::t('Message in chat'),
			2 => ProjectModule::t('Added step'),
	    );
    }
	
	private $_lastPartStatus = null;
	private $_lastPartDate = null;
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return Company::getId().'_Projects';
	}
	public function getFields($role = false) {
		if (!$this->_model || $role) {
			if (get_class(Yii::app())=='CConsoleApplication' || User::model()->isAdmin()) {
				$this->_model=ProjectField::model()->sort()->findAll();
			} elseif (User::model()->isManager()) {
				$this->_model=ProjectField::model()->forManager()->findAll();
			} elseif (User::model()->isCustomer() || $role == 'Customer') {
				$this->_model=ProjectField::model()->forCustomer()->findAll();
			} elseif (User::model()->isAuthor() || Yii::app()->user->isGuest) {
				$this->_model=ProjectField::model()->forAuthor()->findAll();
			}
		}
		return $this->_model;
	}
	
    public function getDbdate_finishstart(){
        if ($this->date_finishstart!='') {
            return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->date_finishstart, $this->dateTimeIncomeFormat));
        }
    }
    public function setDbdate_finishstart($datetime)
    {
        if ($datetime!=''){
            $this->date_finishstart = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    public function getDbdate_finishend(){
        if ($this->date_finishend!='') {
            return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->date_finishend, $this->dateTimeIncomeFormat));
        }
    }
    public function setDbdate_finishend($datetime)
    {
        if ($datetime!=''){
            $this->date_finishend = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    public function getDbdate()
    {
        if ($this->date!='') {
            if ($this->date=='0000-00-00 00:00:00') return '';
            if (strlen($this->date) == 19) return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->date, $this->dateTimeIncomeFormat));
            elseif (strlen($this->date) == 10) return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->date, $this->dateTimeIncomeFormat));
        }
    }
    public function getDbmax_exec_date()
    {
        if ($this->max_exec_date!='') {
            if ($this->max_exec_date=='0000-00-00 00:00:00') return '';
            if (strlen($this->max_exec_date) == 19) return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->max_exec_date, $this->dateTimeIncomeFormat));
            elseif (strlen($this->max_exec_date) == 10) return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->max_exec_date, $this->dateTimeIncomeFormat));
        }
    }
    public function getDbmanager_informed()
    {
        if ($this->manager_informed!='') {
            if ($this->manager_informed=='0000-00-00 00:00:00') return '';
            if (strlen($this->manager_informed) == 19) return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->manager_informed, $this->dateTimeIncomeFormat));
            elseif (strlen($this->manager_informed) == 10) return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->manager_informed, $this->dateTimeIncomeFormat));
        }
    }
    public function getDbdate_finish()
    {
        if ($this->date_finish!='') {
            if ($this->date_finish=='0000-00-00 00:00:00') return '';
            if (strlen($this->date_finish) == 19) return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->date_finish, $this->dateTimeIncomeFormat));
            elseif (strlen($this->date_finish) == 10) return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->date_finish, $this->dateTimeIncomeFormat));
        }
    }
    public function getDbauthor_informed()
    {
        if ($this->author_informed!='') {
            if ($this->author_informed=='0000-00-00 00:00:00') return '';
            if (strlen($this->author_informed) == 19) return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->author_informed, $this->dateTimeIncomeFormat));
            elseif (strlen($this->author_informed) == 10) return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->author_informed, $this->dateTimeIncomeFormat));
        }
    }
    public function setDbmax_exec_date($datetime)
    {
        if ($datetime!=''){
            if (strlen($datetime) == 16) $this->max_exec_date = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
            elseif (strlen($datetime) == 10) $this->max_exec_date = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    public function setDbdate_finish($datetime)
    {
        if ($datetime!=''){
            if (strlen($datetime) == 16) $this->date_finish = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
            elseif (strlen($datetime) == 10) $this->date_finish = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    public function setDbdate($datetime)
    {
        if ($datetime!=''){
            if (strlen($datetime) == 16) $this->date = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
            elseif (strlen($datetime) == 10) $this->date = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    public function setDbmanager_informed($datetime)
    {
        if ($datetime!=''){
            if (strlen($datetime) == 16) $this->manager_informed = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
            elseif (strlen($datetime) == 10) $this->manager_informed = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    public function setDbauthor_informed($datetime)
    {
        if ($datetime!=''){
            if (strlen($datetime) == 16) $this->author_informed = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
            elseif (strlen($datetime) == 10) $this->author_informed = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
        }
    }
    /*public function getJobName()
    {
        if ($this->_job_name === null && $this->job !== null)
        {
            $this->_job_name = $this->job->job_name;
        }
        return $this->_job_name;
    }
    public function setJobName($value)
    {
        $this->_job_name = $value;
    }
    public function getCatName()
    {
        if ($this->_cat_name === null && $this->job !== null)
        {
            $this->_cat_name = $this->category->cat_name;
        }
        return $this->_cat_name;
    }
    public function setCatName($value)
    {
        $this->_cat_name = $value;
    }*/
    public function getStatusName()
    {
        if ($this->_status_name === null && $this->projectStatus !== null)
        {
            $this->_status_name = $this->projectStatus->status;
        }
        return $this->_status_name;
    }
    public function setStatusName($value)
    {
        $this->_status_name = $value;
    }
	public function getClosestDate(){
		$date = $this->author_informed;
		$parts = ZakazParts::model()->findAllByAttributes( array('proj_id'=>$this->id) );
		foreach ($parts as $part){
			if($part->date < $date) $date = $part->date;
		}
		return $date;
	}
	
	public function getLastPartStatus()
	{
		if ($this->_lastPartStatus === null && $this->parts !== null)
		{
			if ($this->parts[0]->status_id != PartStatus::COMPLETED){
				$this->_lastPartStatus = PartStatus::getStatus($this->parts[0]->status_id);
			}
			else {
				$this->_lastPartStatus = '';
			}
		}
		return $this->_lastPartStatus;
	}
	public function setLastPartStatus($value)
	{
		$this->_lastPartStatus = $value;
	}

	public function getLastPartDate()
	{
		if ($this->_lastPartDate === null && $this->parts !== null && $this->parts[0]->status_id != PartStatus::COMPLETED)
		{
			$this->_lastPartDate = $this->parts[0]->date;
		}
		if ($this->_lastPartDate != null) {
			if ($this->_lastPartDate == '0000-00-00 00:00:00') return '';
			if (strlen($this->_lastPartDate) == 19) return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->_lastPartDate, $this->dateTimeIncomeFormat));
			elseif (strlen($this->_lastPartDate) == 10) return Yii::app()->dateFormatter->format($this->dateOutcomeFormat, CDateTimeParser::parse($this->_lastPartDate, $this->dateTimeIncomeFormat));
		}
		return $this->_lastPartDate;
	}
	public function setLastPartDate($datetime)
	{
		if ($datetime!=''){
			if (strlen($datetime) == 16) $this->_lastPartDate = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
			elseif (strlen($datetime) == 10) $this->_lastPartDate = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateOutcomeFormat));
		}
	}

	public function getExecutorEvents(){
		if ($this->executor_event)
		{
			$events = explode(",", $this->executor_event);
			$eventsName = [];
			foreach ($events as $item)
				$eventsName[] = $this->executorEventsArr()[$item];
			return implode(",<br />", $eventsName);
		}
	}

	public function getCustomerEvents(){
		if ($this->customer_event)
		{
			$events = explode(",", $this->customer_event);
			$eventsName = [];
			foreach ($events as $item)
				$eventsName[] = $this->customerEventsArr()[$item];
			return implode(",<br />", $eventsName);
		}
	}

	public function setExecutorEvents($eventId){
		if ($this->executor_event)
        {
            $events = explode(",", $this->executor_event);
            if (!in_array($eventId, $events))
            {
                $events[] = $eventId;
                $this->executor_event = implode(",", $events);
            }
        }
        else
        	$this->executor_event = $eventId;
        $this->save(false);
	}

	public function setCustomerEvents($eventId){
		if ($this->customer_event)
        {
            $events = explode(",", $this->customer_event);
            if (!in_array($eventId, $events))
            {
                $events[] = $eventId;
                $this->customer_event = implode(",", $events);
            }
        }
        else
        	$this->customer_event = $eventId;
        $this->save(false);
	}

    public function init()
    {
        parent::init();
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		if (!$this->_rules) {
			$required = array();
			$numerical = array();
			$float = array();
			$decimal = array();
			$rules = array();
			$fields = '';

			$model=$this->getFields();
			foreach ($model as $field) {
				$field_rule = array();
				$fields .= ' ,'.$field->varname;
				if ($field->required==ProfileField::REQUIRED_YES_NOT_SHOW_REG||$field->required==ProfileField::REQUIRED_YES_SHOW_REG)
					array_push($required,$field->varname);
				if ($field->field_type=='FLOAT')
					array_push($float,$field->varname);
				if ($field->field_type=='DECIMAL')
					array_push($decimal,$field->varname);
				if ($field->field_type=='INTEGER' || $field->field_type=='BOOL')
					array_push($numerical,$field->varname);
				if ($field->field_type=='VARCHAR' || $field->field_type=='TEXT' || $field->field_type=='LIST') {
					$field_rule = array($field->varname, 'length', 'max'=>(($field->field_type=='TEXT' || $field->field_type=='LIST')?65535:$field->field_size), 'min' => 0);
					if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
					array_push($rules,$field_rule);
				}
				if ($field->field_type=='DATE') {
					$field_rule = array($field->varname, 'type', 'type' => 'date', 'dateFormat' => 'yyyy-mm-dd', 'allowEmpty'=>true);
					if ($field->error_message) $field_rule['message'] = UserModule::t($field->error_message);
					array_push($rules,$field_rule);
				}
			}

			// include static fields
			$fields .= ' , technicalspec';
			array_push($numerical, 'technicalspec');
			array_push($numerical, 'status');
			array_push($numerical, 'user_id');
			array_push($numerical, 'executor');
			array_push($numerical, 'parent_id');

			array_push($rules,array(implode(',',$required), 'required'));
			array_push($rules,array(implode(',',$numerical), 'numerical', 'integerOnly'=>true));
			array_push($rules,array(implode(',',$float), 'type', 'type'=>'float'));
			array_push($rules,array(implode(',',$decimal), 'match', 'pattern' => '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/'));
			array_push($rules,array('dbmax_exec_date, dbmanager_informed, dbauthor_informed,unixtime', 'safe'));
			array_push($rules,array('id, dbdate, dbmanager_informed, lastPartStatus, lastPartDate,'.$fields, 'safe', 'on'=>'search'));
			array_push($rules,array('dbmax_exec_date, dbmanager_informed, dbauthor_informed,unixtime, executor_event, customer_event', 'safe'));
			array_push($rules,array('id, dbdate, dbmanager_informed'.$fields, 'safe', 'on'=>'search'));
			$this->_rules = $rules;
		}
		return $this->_rules;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		$relations = array(
			'user' => array(self::HAS_ONE, 'User', array('id'=>'user_id')),
			'author' => [self::BELONGS_TO, 'User', 'executor'],
			'projectStatus'=>array(self::BELONGS_TO, 'ProjectStatus', 'status'),
			'images' => [self::HAS_MANY, 'PaymentImage', 'project_id'],
			'parts' => array(self::HAS_MANY, 'ZakazParts', 'proj_id'),
			'parent' => array(self::HAS_ONE, 'Zakaz', array('id'=>'parent_id')),
			//'catalog_spec1' => [self::BELONGS_TO, 'Catalog', 'specials'],
			//'catalog_spec2' => [self::BELONGS_TO, 'Catalog', 'specials2'],
		);
		$projectFields = $this->getFields();
		if ($projectFields) {
			foreach($projectFields as $field) {
				if ($field->field_type=="LIST"){
					$varname = $field->varname;
					$relations['catalog_'.$varname] = array(self::HAS_ONE, 'Catalog', array('id'=>$varname));
				}
			}
		}
		return $relations;
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		$tmp = array(
			'id' => ProjectModule::t('Order number'),
			'user_id' => ProjectModule::t('User'),
			'date' => ProjectModule::t('Order date'),
			'max_exec_date' => ProjectModule::t('Deadline'),
			'status' => ProjectModule::t('Status'),
			'executor' => ProjectModule::t('Executor'),
			'manager_informed' => ProjectModule::t('Reminder'),
			'author_informed' => ProjectModule::t('The deadline for the executor'),
			'deadline' => ProjectModule::t('Deadline'),
			'notes' => ProjectModule::t('Notes for manager'),
			'author_notes' => ProjectModule::t('author_notes'),
			'closestDate' => ProjectModule::t('closestDate'),
			'technicalspec' => ProjectModule::t('technicalspec'),
			'lastPartStatus' => ProjectModule::t('lastPartStatus'),
			'lastPartDate' => ProjectModule::t('lastPartDate'),
			'executor_event' => ProjectModule::t('executor_event'),
			'customer_event' => ProjectModule::t('customer_event'),
		);
		$projectFields = $this->getFields();
		if ($projectFields) {
			foreach($projectFields as $field) {
				$tmp[$field->varname] = $field->title;
			}
		}
		return $tmp;
	}

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
    public function search_upd()
    {
        $criteria = new CDbCriteria;
		if(!Company::getId()){
        $criteria->with = array('job', 'category');
		}
        $criteria->offset=$this->id;
        $sort = new CSort();
        $sort->defaultOrder = 't.id ASC';
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'sort'=>$sort,
            'pagination'=>array('pageSize'=>1),
        ));
    }
	public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;
		$criteria->compare('t.id', $this->id);
		$criteria->with = array('parts' => array('select' => 'parts.date, parts.status_id', 'order' => 'parts.date'));
		$criteria->together = true;
		$criteria->compare('parts.status_id', $this->lastPartStatus, true);
		$criteria->compare('DATE_FORMAT(max_exec_date, "%d.%m.%Y")', substr($this->dbmax_exec_date,0,10), true);
		$criteria->compare('DATE_FORMAT(author_informed, "%d.%m.%Y")', substr($this->dbauthor_informed,0,10), true);
		$criteria->compare('DATE_FORMAT(manager_informed, "%d.%m.%Y")', substr($this->dbmanager_informed,0,10),true);
		$criteria->compare('DATE_FORMAT(parts.date, "%d.%m.%Y")', substr($this->lastPartDate,0,10),true);
		$fields=$this->getFields();
		foreach ($fields as $field) {
			$tmp = $field->varname;
			if (isset($this->$tmp) && $field->field_type == 'LIST' && $this->$tmp != '') {
				$criteria->compare('t.'.$tmp, explode(',',$this->$tmp));
			} elseif ($field->field_type == 'VARCHAR' || $field->field_type == 'TEXT') {
				$criteria->compare('t.'.$tmp, $this->$tmp, true);
			} else {
				$criteria->compare('t.'.$tmp, $this->$tmp);
			}
		}
		if (!($this->status) or $this->status == 0){            /// Так ли делать
			$criteria->addNotInCondition('status', array(5));
		} else if ($this->status == -1) {
			// show all
		} else {
			$criteria->compare('status',$this->status);
		}

		$sort = new CSort();
		$sort->defaultOrder = 't.id ASC';
		$sort->attributes = array(
			'dateCreation'=> array(
				'asc' => 't.date',
				'desc' => 't.date desc',
			),
			'managerInformed'=> array(
				'asc' => 't.manager_informed',
				'desc' => 't.manager_informed desc',
			),
			'dateFinish'=> array(
				'asc' => 't.date_finish',
				'desc' => 't.date_finish desc',
			),
			'*'
		);

		$dataProvider = new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>$sort,
			'pagination'=>false,
		));
		$data = $dataProvider->data;
		$keys = $dataProvider->keys;
		for ($i=0; $i < count($data); $i++) {
			if($data[$i]->parts[0]->date != ZakazParts::model()->getDateLastUncompleted($data[$i]->id)) {
				for ($j = $i; $j < count($data)-1; $j++) {
					$data[$j] = $data[$j + 1];
					$keys[$j] = $keys[$j + 1];
				}
				unset($data[count($data) - 1]);
				unset($keys[count($keys) - 1]);
			}
		}
		$dataProvider->data = $data;
		$dataProvider->keys = $keys;
        return $dataProvider;
	}

    public static function getExecutor($orderId) {
        return self::model()->findByPk($orderId)->executor;
    }
    public static function getPaymentImage($orderId) {
        return self::model()->findByPk($orderId)->payment_image;
    }
	public function timestampInput($field) {
		$varname = $field->varname;
		if (isset($this->$varname) && $this->$varname != ''){
			$this->$varname = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($this->$varname, $this->dateTimeOutcomeFormat));
		}
	}
	public function timestampOutput($field) {
		$varname = $field->varname;
		if (isset($this->$varname) && $this->$varname != ''){
			$this->$varname = Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->$varname, $this->dateTimeIncomeFormat));
		}
	}
	
    public function moveFiles($unixtime/*,$id*/) {  // Перенести файлы из временной директории в постоянную при сохpании нового заказа
		$id = $this->id;
        $c_id = Company::getId();
        $root = Yii::getPathOfAlias('webroot');
        if ($c_id) {
            $from = $root.'/uploads/c'.$c_id.'/temp/'.$unixtime.'/';
        } else {
            $from = $root.'/uploads/temp/'.$unixtime.'/';
        }
        if (file_exists($from)) {
            $dir_handle = opendir($from);
            if ($c_id) {
                $to = $root.'/uploads/c'.$c_id.'/'.$id.'/';
            } else {
                $to = $root.'/uploads/'.$id.'/';
            }
            if (!file_exists($to)) {
                mkdir($to, 0777);
            }                    
            while ($file = readdir($dir_handle)) {
               if ($file === '.' || $file === '..' || is_dir($file)) continue;
               rename($from.$file, $to.$file);   
            }
            rmdir($from);                    
            
        }
    }
	
	public function generateMaterialsList($url, $for_guests = false, $cant_remove = false) { // генерируем список загруженных материалов заказа
		$path = Yii::getPathOfAlias('webroot') . $url;
		$html_string = '';
		//if (!file_exists($path)) mkdir($path,0755,true);
		if (file_exists($path)){
			foreach (array_diff(scandir($path), array('..', '.')) as $k => $v)
				if ((!strstr($v, '#pre#') || User::model()->isCustomer() || ($for_guests && Yii::app()->user->isGuest)) && !strstr($v, '#trash#')) {
					$tmp = '';
					if(strstr($v, '#pre#')) {
						$tmp = ' class="gray-file"';
						$v0 = substr($v,5);
					} else {
						$v0 = $v;
					}
					$html_string .= '<li'.$tmp.'><a id="j-file-'.$k.'" target="_blank" href="' . $url . rawurlencode($v) . '" class="file" >' . $v0 . '</a>';
					if (!$cant_remove && User::model()->isCustomer()) $html_string .= '<a href="#" data-link="j-file-'.$k.'" data-dir="' . $url . '"  data-name="' . $v . '" onclick="removeFile(this); return false"><i class="glyphicon glyphicon-remove" title="'. Yii::t('site', 'Delete') .'"></i></a>';
					$html_string .= '</li><br />'."\n";
				}
		}
		return $html_string;
	}
    /*public function beforeValidate() {

		if(Campaign::getId()){
			$projectFields = $this->getFields();
			if ($projectFields) {
				foreach($projectFields as $field) {
					if ($field->field_type=="TIMESTAMP") {
						$this->timestampInput($field);
					}
				}
			}
		}

        return parent::beforeValidate();
    }*/
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Zakaz the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function defaultScope()
    {
        return [
            'condition' => 'is_active = 1'
        ];
    }
    
    public function behaviors()
    {
        return [
            'ModerateBehavior' => [
                'class' => 'ModerateBehavior'
            ]
        ];
    }
}
